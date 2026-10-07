<?php

namespace Tests\Feature;

use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_destination_has_its_own_guide_page_with_faq_data(): void
    {
        $this->get('/destinations/kyoto')->assertOk()
            ->assertSee('<title>Kyoto travel guide: best time, how many days &amp; visa for Filipinos · Travel2gether</title>', false)
            ->assertSee('When is the best time to visit Kyoto?')
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Plan my Kyoto trip — free');

        $this->get('/destinations/atlantis')->assertNotFound();
    }

    public function test_the_sitemap_lists_guides_and_samples_but_not_private_pages(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('destinations.show', 'kyoto'), false)
            ->assertSee(route('trips.show', 'seoul-2026'), false)
            ->assertDontSee('/dashboard', false)
            ->assertDontSee('/login', false);
    }

    public function test_sign_in_pages_are_kept_out_of_search_results(): void
    {
        $this->get(route('login'))->assertSee('<meta name="robots" content="noindex">', false);
        $this->get(route('register'))->assertSee('<meta name="robots" content="noindex">', false);
        $this->get('/')->assertDontSee('content="noindex"', false)->assertSee('"@type":"WebSite"', false);
    }

    public function test_sample_trips_get_a_search_friendly_title_and_canonical(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get(route('trips.show', 'seoul-2026'))->assertOk()
            ->assertSee('-day Seoul itinerary', false)
            ->assertSee('<link rel="canonical" href="' . route('trips.show', 'seoul-2026') . '">', false)
            ->assertDontSee('content="noindex"', false);
    }
}
