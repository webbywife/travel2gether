<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_privacy_page_is_public_and_lists_the_third_parties(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy', false)
            ->assertSee('Google Gemini', false)
            ->assertSee('Open-Meteo', false)
            ->assertSee('local storage', false);
    }

    public function test_the_cookie_notice_and_privacy_link_render_on_the_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('cookieNotice', false)
            ->assertSee(route('privacy'), false);
    }
}
