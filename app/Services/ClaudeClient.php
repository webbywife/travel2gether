<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\RateLimitException;

/**
 * Thin wrapper around the official Anthropic PHP SDK — the AI behind day
 * drafts, Trippie and the day-details helper. (Gemini stays as a fallback,
 * but Google blocks it from this server's IP.)
 *
 * Errors surface as RuntimeException('quota' | 'refusal' | 'unavailable')
 * so callers keep their existing friendly messages.
 */
class ClaudeClient
{
    public function enabled(): bool
    {
        return filled(config('services.anthropic.api_key'));
    }

    public function model(): string
    {
        return (string) config('services.anthropic.model', 'claude-opus-5-5');
    }

    private function client(float $timeout): Client
    {
        return new Client(
            apiKey: config('services.anthropic.api_key'),
            requestOptions: ['timeout' => $timeout, 'maxRetries' => 2],
        );
    }

    /**
     * One request whose reply must match $schema (structured outputs), decoded.
     *
     * @param  array<string, mixed>  $schema  JSON Schema (objects need additionalProperties:false + required)
     * @return array<string, mixed>
     */
    public function json(string $system, string $user, array $schema, string $effort = 'medium', int $maxTokens = 16000, float $timeout = 170): array
    {
        $text = $this->send(
            system: $system,
            messages: [['role' => 'user', 'content' => $user]],
            outputConfig: ['effort' => $effort, 'format' => ['type' => 'json_schema', 'schema' => $schema]],
            maxTokens: $maxTokens,
            timeout: $timeout,
        );

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
            outputConfig: ['effort' => $effort],
            maxTokens: $maxTokens,
            timeout: $timeout,
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $outputConfig
     */
    private function send(string $system, array $messages, array $outputConfig, int $maxTokens, float $timeout): string
    {
        try {
            $message = $this->client($timeout)->beta->messages->create(
                model: $this->model(),
                maxTokens: $maxTokens,
                system: [['type' => 'text', 'text' => $system, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: $messages,
                outputConfig: $outputConfig,
                // If the model declines on policy grounds, let the API retry on its default fallback.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (RateLimitException $e) {
            throw new \RuntimeException('quota', 0, $e);
        } catch (APIStatusException $e) {
            report($e);
            throw new \RuntimeException('unavailable', 0, $e);
        } catch (APIConnectionException $e) {
            report($e);
            throw new \RuntimeException('unavailable', 0, $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new \RuntimeException('refusal');
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return $block->text;
            }
        }

        throw new \RuntimeException('unavailable');
    }
}
