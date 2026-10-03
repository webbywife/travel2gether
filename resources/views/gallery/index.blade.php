@extends('layouts.site')

@section('title', 'From my travels · Travel2gether')
@section('meta_description', 'Photos from the places behind Travel2gether — ' . count($places) . ' destinations, all taken on real trips.')

@push('styles')
<style>
  .gal-hero{max-width:900px; margin:0 auto; padding:48px 24px 12px; text-align:center;}
  .gal-hero h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,42px); margin:0 0 12px; letter-spacing:-0.02em;}
  .gal-hero p{color:var(--text-dim); max-width:58ch; margin:0 auto;}
  .gal-stats{display:flex; justify-content:center; gap:28px; margin:22px 0 4px; flex-wrap:wrap;}
  .gal-stats b{display:block; font-family:'Space Grotesk',sans-serif; font-size:26px; color:var(--pink); line-height:1.1;}
  .gal-stats span{font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-dim);}

  .gal-tools{max-width:1120px; margin:20px auto 22px; padding:0 24px; display:flex; justify-content:center;}
  .gal-tools input{width:min(420px,100%); font:15px Inter,sans-serif; padding:11px 16px; border-radius:999px; border:1px solid var(--line); background:#fff;}
  .gal-tools input:focus{outline:2px solid var(--pink-light); outline-offset:1px;}

  .place-grid{max-width:1120px; margin:0 auto; padding:0 24px 72px; display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:16px;}
  .place-card{position:relative; display:block; border-radius:16px; overflow:hidden; text-decoration:none; color:#fff;
    aspect-ratio:4/3; background:var(--panel-2); box-shadow:var(--shadow-sm); transition:transform .16s ease, box-shadow .16s ease;}
  .place-card:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .place-card img{width:100%; height:100%; object-fit:cover; display:block;}
  .place-card .shade{position:absolute; inset:auto 0 0 0; padding:44px 16px 14px; background:linear-gradient(transparent, rgba(20,12,18,0.78));}
  .place-card h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:0; line-height:1.2; letter-spacing:-0.01em;}
  .place-card .meta{font-size:12.5px; opacity:0.88; margin-top:3px;}
  .place-card .count{position:absolute; top:12px; right:12px; font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700;
    background:rgba(255,255,255,0.92); color:var(--pink); border-radius:999px; padding:4px 9px;}
  .gal-empty{text-align:center; color:var(--text-dim); padding:40px 24px 80px;}
  @media (max-width:560px){ .place-grid{grid-template-columns:1fr 1fr; gap:10px; padding:0 16px 56px;} .place-card h3{font-size:15px;} .place-card .meta{display:none;} }
</style>
@endpush

@section('content')
<header class="gal-hero reveal">
  <h1><span class="gtext">From my travels</span></h1>
  <p>Every photo here was taken on a real trip — the places behind the itineraries. Tap a place to see the whole set.</p>
  @if($total)
  <div class="gal-stats">
    <div><b>{{ number_format($total) }}</b><span>photos</span></div>
    <div><b>{{ count($places) }}</b><span>places</span></div>
    <div><b>{{ collect($places)->pluck('country')->filter()->unique()->count() }}</b><span>countries</span></div>
  </div>
  @endif
</header>

@if(count($places))
  <div class="gal-tools"><input type="search" id="galFilter" placeholder="Find a place — e.g. Kyoto, Japan, Baguio" aria-label="Filter places"></div>

  <div class="place-grid" id="placeGrid">
    @foreach($places as $p)
      @php
        $years = $p['years'];
        $span = count($years) ? (count($years) > 1 ? reset($years) . '–' . end($years) : reset($years)) : '';
      @endphp
      <a class="place-card reveal" href="{{ route('gallery.show', $p['slug']) }}" data-search="{{ \Illuminate\Support\Str::lower($p['name'] . ' ' . $p['country']) }}">
        <img src="{{ \App\Support\Gallery::cover($p['slug']) }}" alt="{{ $p['name'] }}" loading="lazy" decoding="async">
        <span class="count">{{ $p['count'] }}</span>
        <div class="shade">
          <h3>{{ $p['name'] }}</h3>
          <div class="meta">{{ $p['country'] }}@if($span) · {{ $span }}@endif</div>
        </div>
      </a>
    @endforeach
  </div>
  <p class="gal-empty" id="galNone" hidden>No place matches that.</p>

  <script>
  (function () {
    var input = document.getElementById('galFilter');
    var cards = document.querySelectorAll('#placeGrid .place-card');
    var none = document.getElementById('galNone');
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase(), shown = 0;
      cards.forEach(function (c) { var on = !q || c.dataset.search.indexOf(q) !== -1; c.style.display = on ? '' : 'none'; if (on) shown++; });
      none.hidden = shown !== 0;
    });
  })();
  </script>
@else
  <p class="gal-empty">Photos are on their way.</p>
@endif
@endsection
