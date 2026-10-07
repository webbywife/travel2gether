@extends('layouts.site')

@php
  use App\Support\Destinations;
  use App\Support\Gallery;
  use Illuminate\Support\Str;

  $name = $t['destination'];
  $visaLabel = $visa ? Destinations::visaLabel($visa['visa_status']) : null;
  $planUrl = route('trips.create', ['destination' => $name . ', ' . $t['country']]);
  $photos = collect($gallery['photos'] ?? [])->where('type', 'photo')->take(6);
  $cover = Gallery::cover($slug) ?? (file_exists(public_path("img/destinations/{$slug}.jpg")) ? asset("img/destinations/{$slug}.jpg") : null);

  // Every answer below is shown on the page too (FAQ structured data must match visible content).
  $faq = array_values(array_filter([
      ['q' => "When is the best time to visit {$name}?", 'a' => $t['best_season'] . '.'],
      ['q' => "How many days do you need in {$name}?", 'a' => Str::ucfirst($t['trip_length']) . ' is a comfortable first trip.'],
      $visa ? ['q' => "Do Filipinos need a visa for {$t['country']}?", 'a' => $visaLabel . ' for a Philippine passport. ' . $visa['visa_note'] . ' Rules change — confirm with the embassy before booking.'] : null,
  ]));

  $ld = [
      [
          '@context' => 'https://schema.org', '@type' => 'TouristDestination',
          'name' => "{$name}, {$t['country']}", 'description' => $t['overview'], 'url' => url()->current(),
          'containedInPlace' => ['@type' => 'Country', 'name' => $t['country']],
      ] + ($cover ? ['image' => $cover] : []),
      [
          '@context' => 'https://schema.org', '@type' => 'FAQPage',
          'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faq),
      ],
      [
          '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
          'itemListElement' => [
              ['@type' => 'ListItem', 'position' => 1, 'name' => 'Destinations', 'item' => route('destinations')],
              ['@type' => 'ListItem', 'position' => 2, 'name' => $name, 'item' => url()->current()],
          ],
      ],
  ];
@endphp

@section('title', "{$name} travel guide: best time, how many days & visa for Filipinos · Travel2gether")
@section('meta_description', Str::limit("{$name}, {$t['country']}: best time to visit ({$t['best_season']}), how many days you need ({$t['trip_length']})" . ($visaLabel ? ", and visa for a Philippine passport ({$visaLabel})" : '') . ". Plan a day-by-day {$name} itinerary with your group — free.", 300))
@if ($cover)
  @section('og_image', $cover)
@endif

@push('head')
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@push('styles')
<style>
  .dg{max-width:900px; margin:0 auto; padding:36px 24px 64px;}
  .dg-crumb{font-size:13px; color:var(--text-dim);}
  .dg-crumb a{color:var(--text-dim);}
  .dg h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(30px,4.4vw,46px); margin:10px 0 4px; letter-spacing:-0.02em;}
  .dg .sub{color:var(--text-dim); margin:0 0 22px;}
  .dg-cover{width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:16px; display:block; margin-bottom:22px;}
  .dg-facts{display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:12px; margin-bottom:22px;}
  .dg-fact{background:rgba(255,255,255,0.92); border:1px solid var(--line); border-radius:14px; padding:14px 16px;}
  .dg-fact b{display:block; font-family:'JetBrains Mono',monospace; font-size:10.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-dim); font-weight:500; margin-bottom:4px;}
  .dg-fact span{font-size:15px;}
  .dg-overview{font-size:17px; line-height:1.65; margin:0 0 24px;}
  .dg-cta{background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:18px 20px; margin:0 0 30px; display:flex; flex-wrap:wrap; gap:10px 18px; align-items:center; justify-content:space-between;}
  .dg-cta p{margin:0; color:var(--text-dim); font-size:14.5px; max-width:52ch;}
  .dg h2{font-family:'Space Grotesk',sans-serif; font-size:22px; margin:32px 0 12px;}
  .dg-photos{display:grid; grid-template-columns:repeat(3,1fr); gap:8px;}
  .dg-photos img{width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px; display:block;}
  .dg-faq details{border-bottom:1px solid var(--line); padding:12px 0;}
  .dg-faq summary{cursor:pointer; font-weight:600;}
  .dg-faq p{margin:8px 0 0; color:var(--text-dim); line-height:1.6;}
  .dg-related{display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px;}
  .dg-related a{display:block; border:1px solid var(--line); border-radius:12px; padding:12px 14px; background:#fff; color:var(--text); text-decoration:none;}
  .dg-related a:hover{border-color:var(--pink-light);}
  .dg-related small{display:block; color:var(--text-dim); margin-top:2px;}
  .dg-note{font-size:12.5px; color:var(--text-dim); margin-top:26px;}
  @media (max-width:560px){ .dg-photos{grid-template-columns:repeat(2,1fr);} .dg-cta .btn{width:100%; text-align:center;} }
</style>
@endpush

@section('content')
<article class="dg">
  <nav class="dg-crumb"><a href="{{ route('destinations') }}">Destinations</a> › {{ $name }}</nav>
  <h1><span class="gtext">{{ $name }}</span> travel guide</h1>
  <p class="sub">{{ $t['country'] }} · {{ $t['region'] }} · {{ $t['category'] }}</p>

  @if ($cover)
    <img class="dg-cover" src="{{ $cover }}" alt="{{ $name }}, {{ $t['country'] }}" fetchpriority="high">
  @endif

  <div class="dg-facts">
    <div class="dg-fact"><b>Best time to visit</b><span>{{ $t['best_season'] }}</span></div>
    <div class="dg-fact"><b>How many days</b><span>{{ $t['trip_length'] }}</span></div>
    @if ($visa)<div class="dg-fact"><b>Philippine passport</b><span>{{ $visaLabel }}</span></div>@endif
  </div>

  <p class="dg-overview">{{ $t['overview'] }}</p>

  <div class="dg-cta">
    <p>Get a day-by-day {{ $name }} itinerary — options for every time slot, a live budget and rainy-day backups — that your group shapes together.</p>
    <a class="btn btn-primary" href="{{ $planUrl }}">Plan my {{ $name }} trip — free</a>
  </div>

  @if ($photos->isNotEmpty())
    <h2>{{ $name }} in my photos</h2>
    <div class="dg-photos">
      @foreach ($photos as $p)
        <a href="{{ route('gallery.show', $slug) }}"><img src="{{ Gallery::thumb($slug, $p) }}" alt="{{ $p['caption'] ?: $name }}" loading="lazy" decoding="async"></a>
      @endforeach
    </div>
    <p style="margin-top:10px;"><a href="{{ route('gallery.show', $slug) }}">See all {{ $gallery['count'] }} photos from {{ $name }} →</a></p>
  @endif

  <h2>{{ $name }} questions</h2>
  <div class="dg-faq">
    @foreach ($faq as $f)
      <details @if($loop->first) open @endif><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
    @endforeach
  </div>

  @if ($related->isNotEmpty())
    <h2>More trips like this</h2>
    <div class="dg-related">
      @foreach ($related as $r)
        <a href="{{ route('destinations.show', Destinations::slug($r)) }}">{{ $r['destination'] }}<small>{{ $r['country'] }} · {{ $r['trip_length'] }}</small></a>
      @endforeach
    </div>
  @endif

  <p class="dg-note">Visa rules change and have exceptions — treat this as a starting point and confirm with the embassy or official immigration site before booking.</p>
</article>
@endsection
