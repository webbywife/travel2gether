<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Trippie — the Travel2gether planning buddy. A short, upbeat chat over Gemini
 * that helps people figure out destinations, how many days, where to base,
 * seasons, transport and rough budgets while they build an itinerary.
 */
class TrippieAssistant
{
    private const PERSONA = <<<'SYS'
    You are Trippie, the friendly planning buddy inside Travel2gether — an app where
    groups build day-by-day trip itineraries together.

    Voice: warm, upbeat and a little playful. Sound like an excited friend who has
    actually been there. A well-placed emoji is fine; don't spray them.

    Substance over hype. Give concrete, usable answers: real place and
    neighbourhood names, how many days something needs, which area to base in and
    why, the season trade-offs, how to get between places, and rough budget bands.
    Always add a practical tip or a rainy-day backup when it fits.

    Keep it tight — 2 to 5 sentences, or a short bullet list. No long essays.

    Never state flight numbers, exact prices or opening hours as hard facts — give
    ranges and say to check current info.

    You only help with trip planning. If someone goes off-topic, answer in one
    breezy line and pull it back to their trip.

    If you're told the user's current trip details, tailor everything to it.
    SYS;

    private ?string $key;
    private string $model;

    public function __construct()
    {
        $this->key = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.6-flash');
    }

    public function enabled(): bool
    {
        return filled($this->key);
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $history  prior turns, oldest first
     * @param  array<string, mixed>|null  $tripContext
     */
    public function reply(string $message, array $history = [], ?array $tripContext = null): string
    {
        abort_unless($this->enabled(), 503, 'Trippie is not configured.');

        $contents = [];
        foreach (array_slice($history, -10) as $turn) {
            $role = ($turn['role'] ?? 'user') === 'model' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => (string) ($turn['text'] ?? '')]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $system = self::PERSONA;
        if ($tripContext) {
            $system .= "\n\nThe user's current trip:\n" . collect($tripContext)
                ->filter()
                ->map(fn ($v, $k) => "- {$k}: " . (is_array($v) ? implode(', ', $v) : $v))
                ->implode("\n");
        }

        $response = Http::timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->key}",
            [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.85,
                    'maxOutputTokens' => 1024,
                    'thinkingConfig' => ['thinkingBudget' => 512],
                ],
            ],
        );

        if ($response->status() === 429) {
            return "You're quick! 😄 Give me a few seconds and ask again.";
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Trippie call failed: ' . $response->status());
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));

        return $text !== '' ? $text : "Hmm, I blanked for a sec — ask me again? 🧭";
    }
}
