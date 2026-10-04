<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gallery.enabled' => true]);
    }

    public function test_the_gallery_is_hidden_everywhere_while_switched_off(): void
    {
        $this->index([$this->kyoto()]);
        config(['services.gallery.enabled' => false]);

        $this->get(route('gallery'))->assertNotFound();
        $this->get(route('gallery.show', 'kyoto'))->assertNotFound();
        $this->get(route('destinations'))->assertOk()->assertDontSee('of my photos')->assertDontSee('>Gallery</a>', false);
        $this->get(route('home'))->assertOk()->assertDontSee('From my travels');
    }

    private function index(array $places): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/photos.json', json_encode(['places' => $places]));
    }

    private function kyoto(): array
    {
        return [
            'slug' => 'kyoto', 'name' => 'Kyoto', 'country' => 'Japan', 'years' => ['2019', '2024'],
            'photos' => [
                ['file' => 'kyoto-01.jpg', 'w' => 1600, 'h' => 1200, 'place' => 'Fushimi Inari', 'city' => 'Kyoto', 'taken' => '2024-04'],
                ['file' => 'kyoto-02.jpg', 'w' => 1200, 'h' => 1600, 'place' => '', 'city' => 'Kyoto', 'taken' => '2019-11'],
            ],
        ];
    }

    public function test_gallery_lists_places_and_a_place_page_shows_its_photos(): void
    {
        $this->index([$this->kyoto()]);

        $this->get(route('gallery'))->assertOk()
            ->assertSee('From my travels')
            ->assertSee('Kyoto')
            ->assertSee('2019–2024');

        $this->get(route('gallery.show', 'kyoto'))->assertOk()
            ->assertSee('photos/kyoto/t/kyoto-01.jpg', false)
            ->assertSee('Fushimi Inari');
    }

    public function test_unknown_places_404_and_bad_slugs_never_reach_the_disk(): void
    {
        $this->index([$this->kyoto()]);

        $this->get('/gallery/osaka')->assertNotFound();
        $this->get('/gallery/..%2F..%2Fetc')->assertNotFound();
    }

    public function test_hostile_entries_in_the_index_are_dropped_or_escaped(): void
    {
        $evil = $this->kyoto();
        $evil['name'] = '<script>alert(1)</script>';
        $evil['photos'][] = ['file' => '../../.env', 'w' => 1, 'h' => 1];
        $this->index([$evil, ['slug' => '../x', 'name' => 'x', 'photos' => [['file' => 'a.jpg']]]]);

        $page = $this->get(route('gallery'))->assertOk();
        $page->assertDontSee('<script>alert(1)</script>', false);
        $page->assertDontSee('../x', false);

        $this->get(route('gallery.show', 'kyoto'))->assertOk()->assertDontSee('.env', false);
    }

    public function test_without_photos_the_gallery_says_so_and_the_nav_link_is_hidden(): void
    {
        Storage::fake('public');

        $this->get(route('gallery'))->assertOk()->assertSee('Photos are on their way');
        $this->get(route('destinations'))->assertOk()->assertDontSee('>Gallery</a>', false);
    }

    public function test_videos_show_with_their_poster_and_bad_video_entries_are_dropped(): void
    {
        $k = $this->kyoto();
        $k['photos'][] = ['file' => 'kyoto-v001.mp4', 'type' => 'video', 'poster' => 'kyoto-v001.jpg', 'w' => 720, 'h' => 1280, 'taken' => '2024-04'];
        $k['photos'][] = ['file' => 'evil.mp4', 'type' => 'video', 'poster' => '../../x.jpg'];
        $this->index([$k]);

        $this->get(route('gallery.show', 'kyoto'))->assertOk()
            ->assertSee('photos/kyoto/t/kyoto-v001.jpg', false)   // grid uses the poster
            ->assertSee('kyoto-v001.mp4', false)                    // lightbox plays the video
            ->assertSee('1 video')
            ->assertDontSee('evil.mp4', false);
    }

    public function test_instagram_photos_link_out_and_only_to_instagram(): void
    {
        $k = $this->kyoto();
        $k['photos'][0]['link'] = 'https://www.instagram.com/traveleyz/';
        $k['photos'][1]['link'] = 'javascript:alert(1)';
        $this->index([$k]);

        $page = $this->get(route('gallery.show', 'kyoto'))->assertOk()
            ->assertSee('View on Instagram')
            ->assertSee('https:\/\/www.instagram.com\/traveleyz\/', false);
        $page->assertDontSee('javascript:alert', false);
    }

    public function test_the_index_never_exposes_coordinates(): void
    {
        $k = $this->kyoto();
        $k['photos'][0]['lat'] = 35.0;   // even if a stray field slipped in
        $this->index([$k]);

        $this->get(route('gallery.show', 'kyoto'))->assertOk()->assertDontSee('35.0', false);
    }

    public function test_country_codes_and_names_count_as_one_country(): void
    {
        $this->assertSame('Japan', \App\Support\Gallery::countryName('JP'));
        $this->assertSame('United States', \App\Support\Gallery::countryName('USA'));
        $this->assertSame('United States', \App\Support\Gallery::countryName('US'));
        $this->assertSame('Japan', \App\Support\Gallery::countryName('Japan'));
    }
}
