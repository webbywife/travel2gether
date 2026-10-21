<?php

namespace Tests\Feature;

use App\Services\ClaudeClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouterClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openrouter.api_key' => 'sk-or-test', 'services.openrouter.model' => 'vendor/model']);
    }

    private function reply(string $content, string $finish = 'stop'): array
    {
        return ['choices' => [['message' => ['content' => $content], 'finish_reason' => $finish]]];
    }

    public function test_chat_sends_system_and_history_to_openrouter_and_returns_the_text(): void
    {
        Http::fake(['openrouter.ai/*' => Http::response($this->reply(' Hello! '))]);

        $out = app(ClaudeClient::class)->chat('Be nice', [['role' => 'user', 'content' => 'hi']]);

        $this->assertSame('Hello!', $out);
        Http::assertSent(fn (Request $r) => $r->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $r->hasHeader('Authorization', 'Bearer sk-or-test')
            && $r['model'] === 'vendor/model'
            && $r['messages'][0] === ['role' => 'system', 'content' => 'Be nice']
            && $r['messages'][1]['content'] === 'hi');
    }

    public function test_json_decodes_fenced_output_and_states_the_schema(): void
    {
        Http::fake(['openrouter.ai/*' => Http::response($this->reply("```json\n{\"a\":1}\n```"))]);

        $out = app(ClaudeClient::class)->json('sys', 'user', ['type' => 'object']);

        $this->assertSame(['a' => 1], $out);
        Http::assertSent(fn (Request $r) => $r['response_format'] === ['type' => 'json_object']
            && str_contains($r['messages'][0]['content'], '"type":"object"'));
    }

    public function test_errors_map_to_the_friendly_exception_codes(): void
    {
        Http::fake(['openrouter.ai/*' => Http::sequence()
            ->push([], 429)->push([], 500)->push($this->reply('', 'content_filter'))->push($this->reply('not json'))]);
        $c = app(ClaudeClient::class);

        foreach (['quota', 'unavailable', 'refusal'] as $code) {
            try {
                $c->chat('s', [['role' => 'user', 'content' => 'x']]);
                $this->fail("expected {$code}");
            } catch (\RuntimeException $e) {
                $this->assertSame($code, $e->getMessage());
            }
        }

        $this->expectExceptionMessage('unavailable');
        $c->json('s', 'u', ['type' => 'object']);
    }

    public function test_it_is_disabled_without_a_key(): void
    {
        config(['services.openrouter.api_key' => null]);
        $this->assertFalse(app(ClaudeClient::class)->enabled());
    }
}
