<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around OpenRouter's chat-completions API — the AI behind day
 * drafts, Trippie and the day-details helper. (Gemini stays as a fallback,
 * but Google blocks it from this server's IP.) The class keeps its old name so
 * callers and tests are unchanged.
 *
 * Errors surface as RuntimeException('quota' | 'refusal' | 'unavailable')
 * so callers keep their existing friendly messages.
 */
class ClaudeClient
{
    private const ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    public function enabled(): bool
    {
        return filled(config('services.openrouter.api_key'));
    }

    public function model(): string
    {
        return trim((string) config('services.openrouter.model', 'deepseek/deepseek-chat'));
    }

    /**
     * One request whose reply must match $schema, decoded.
     *
     * @param  array<string, mixed>  $schema  JSON Schema
     * @return array<string, mixed>
     */
    public function json(string $system, string $user, array $schema, string $effort = 'medium', int $maxTokens = 16000, float $timeout = 170): array
    {
        // Not every OpenRouter model enforces json_schema, so state the shape in the prompt too.
        $system .= "\n\nReply with a single JSON object only (no markdown fences) matching this JSON Schema:\n".json_encode($schema);

        $text = $this->send(
            system: $system,
            messages: [['role' => 'user', 'content' => $user]],
            maxTokens: $maxTokens,
            timeout: $timeout,
            json: true,
        );

        // Some models wrap JSON in a ```json fence despite the instruction.
        $text = trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($text)));
        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new \RuntimeException('unavailable');
        }

        return $data;
    }

    /**
     * A conversational reply.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(string $system, array $messages, string $effort = 'low', int $maxTokens = 4000, float $timeout = 60): string
    {
        return trim($this->send(
            system: $system,
            messages: $messages,
            maxTokens: $maxTokens,
            timeout: $timeout,
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    private function send(string $system, array $messages, int $maxTokens, float $timeout, bool $json = false): string
    {
        $payload = [
            'model' => $this->model(),
            'max_tokens' => $maxTokens,
            'messages' => array_merge([['role' => 'system', 'content' => $system]], $messages),
        ];
        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withToken((string) config('services.openrouter.api_key'))
                ->withHeaders(['X-Title' => 'Travel2gether'])
                ->timeout((int) ceil($timeout))
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->acceptJson()
                ->post(self::ENDPOINT, $payload);
        } catch (ConnectionException $e) {
            report($e);
            throw new \RuntimeException('unavailable', 0, $e);
        }

        if ($response->status() === 429) {
            throw new \RuntimeException('quota');
        }
        if ($response->failed()) {
            report(new \RuntimeException('OpenRouter HTTP '.$response->status()));
            throw new \RuntimeException('unavailable');
        }

        $choice = $response->json('choices.0');
        if (($choice['finish_reason'] ?? null) === 'content_filter') {
            throw new \RuntimeException('refusal');
        }

        $text = $choice['message']['content'] ?? null;
        if (! is_string($text) || trim($text) === '') {
            throw new \RuntimeException('unavailable');
        }

        return $text;
    }
}
