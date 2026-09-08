<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrippieTest extends TestCase
{
    use RefreshDatabase;

    private function fakeTrippie(string $reply = 'Base in Shinjuku — great transport links. 3-4 days is plenty. 🚅'): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $reply]]]]],
            ]),
        ]);
    }

    public function test_trippie_replies_to_a_guest(): void
    {
        $this->fakeTrippie();

        $this->postJson('/trippie', ['message' => 'How many days for Tokyo?'])
            ->assertOk()
            ->assertJsonPath('reply', 'Base in Shinjuku — great transport links. 3-4 days is plenty. 🚅');
    }

    public function test_it_sends_history_and_trip_context_to_the_model(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $this->fakeTrippie();

        $this->postJson('/trippie', [
            'message' => 'What should I do on day 2?',
            'history' => [
                ['role' => 'user', 'text' => 'hi'],
                ['role' => 'model', 'text' => 'hey! where to?'],
            ],
            'trip_slug' => 'seoul-2026',
        ])->assertOk();

        Http::assertSent(function ($request) {
            $body = $request->data();
            $system = data_get($body, 'systemInstruction.parts.0.text');

            return str_contains($system, 'Seoul, South Korea')          // trip context injected
                && count($body['contents']) === 3                        // 2 history turns + new message
                && data_get($body, 'contents.2.parts.0.text') === 'What should I do on day 2?';
        });
    }

    public function test_a_model_failure_returns_a_friendly_line_not_a_500(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('nope', 500)]);

        $this->postJson('/trippie', ['message' => 'help'])
            ->assertOk()
            ->assertJsonFragment(['reply' => "Whoops, my map folded on me 🗺️ — give that another try in a sec."]);
    }

    public function test_it_503s_when_gemini_is_not_configured(): void
    {
        config(['services.gemini.api_key' => null]);

        $this->postJson('/trippie', ['message' => 'hello'])->assertStatus(503);
    }

    public function test_message_is_required(): void
    {
        $this->fakeTrippie();
        $this->postJson('/trippie', [])->assertStatus(422);
    }
}
