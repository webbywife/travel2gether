@extends('layouts.site')

@section('title', 'Get inspired · Travel2gether')
@section('meta_description', 'Forty destination templates across every continent — category, best season, trip length and a visa note for a Philippine passport.')

@push('styles')
<style>
  .dest-hero{max-width:900px; margin:0 auto; padding:48px 24px 8px; text-align:center;}
  .dest-hero h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,42px); margin:0 0 12px; letter-spacing:-0.02em;}
  .dest-hero p{color:var(--text-dim); max-width:60ch; margin:0 auto;}
  .visa-key{display:flex; justify-content:center; gap:14px; flex-wrap:wrap; margin:22px 0 6px; font-size:12.5px;}
  .visa-key span{display:inline-flex; align-items:center; gap:6px; color:var(--text-dim);}
  .visa-key i{width:9px; height:9px; border-radius:50%; display:inline-block;}
  .visa-note{max-width:820px; margin:18px auto 32px; padding:12px 16px; border:1px solid var(--line); background:var(--panel); border-radius:12px; font-size:12.5px; color:var(--text-dim); text-align:center;}

  .cat-tabs{max-width:1120px; margin:0 auto 28px; padding:0 24px; display:flex; flex-wrap:wrap; gap:8px; justify-content:center;}
  .cat-tabs button{font-family:'JetBrains Mono',monospace; font-size:11.5px; letter-spacing:0.03em; padding:8px 15px; border-radius:999px; border:1px solid var(--line); background:#fff; color:var(--text-dim); cursor:pointer;}
  .cat-tabs button:hover{border-color:var(--pink-light); color:var(--text);}
  .cat-tabs button.on{background:var(--grad-soft); color:#fff; border-color:transparent;}

  .tpl-grid{max-width:1120px; margin:0 auto; padding:0 24px 64px; display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px;}
  .tpl-card{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.93); box-shadow:var(--shadow-sm); overflow:hidden; display:flex; flex-direction:column; transition:transform .16s ease, box-shadow .16s ease;}
  .tpl-card:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .tpl-photo{width:100%; aspect-ratio:16/10; object-fit:cover; display:block; background:var(--panel-2);}
  .tpl-body{padding:20px; display:flex; flex-direction:column; flex:1;}
  .tpl-top{display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px;}
  .cat-tag{font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.06em; text-transform:uppercase; font-weight:700; color:var(--lavender); background:rgba(113,86,168,0.1); border-radius:999px; padding:4px 10px;}
  .continent-tag{font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.05em; text-transform:uppercase; color:var(--text-dim);}
  .tpl-card h3{font-family:'Space Grotesk',sans-serif; font-size:19px; margin:0 0 2px; letter-spacing:-0.01em;}
  .tpl-card .place{font-size:12.5px; color:var(--text-dim); margin-bottom:10px;}
  .visa-pill{display:inline-flex; align-items:center; gap:5px; font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; letter-spacing:0.03em; text-transform:uppercase; border-radius:999px; padding:3px 9px; margin-bottom:10px; align-self:flex-start;}
  .visa-pill.visa_free{background:rgba(59,167,118,0.12); color:#2f6d54;}
  .visa-pill.evisa{background:rgba(113,86,168,0.12); color:#5a4488;}
  .visa-pill.required{background:rgba(194,42,102,0.1); color:var(--accent);}
  .tpl-meta{display:flex; flex-wrap:wrap; gap:10px; font-size:12px; color:var(--text-dim); margin-bottom:10px;}
  .tpl-meta b{color:var(--text); font-weight:600;}
  .tpl-card .overview{font-size:13px; color:var(--text-dim); line-height:1.55; margin:0 0 14px; flex:1;}
  .tpl-card .cta{margin-top:auto; align-self:flex-start; font-size:13px; font-weight:700; text-decoration:none; color:var(--pink);}
  .tpl-card .cta:hover{text-decoration:underline;}
  @media (max-width:560px){ .tpl-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<header class="dest-hero reveal">
  <h1><span class="gtext">Get inspired</span></h1>
  <p>Forty destination templates across every continent — the kind of place, the best season to go, a sensible trip length, and what makes it worth it. Tap one to start planning it. Visa notes are for a <strong>Philippine passport</strong>, since that's the trip most people here are flying from.</p>
  <div class="visa-key">
    <span><i style="background:#3BA776"></i> Visa-free</span>
    <span><i style="background:#7156A8"></i> e-Visa</span>
    <span><i style="background:#C22A66"></i> Visa required</span>
  </div>
</header>

<p class="visa-note">Visa rules change and have exceptions (transit rules, accredited-agency schemes, holding a qualifying third-country visa, and more) — treat these as a starting point and confirm with the destination's embassy or official immigration site before booking anything.</p>

<div class="cat-tabs" id="catTabs">
  <button type="button" class="on" data-cat="all">All ({{ count($templates) }})</button>
  @foreach ($categories as $cat)
    <button type="button" data-cat="{{ $cat }}">{{ $cat }} ({{ collect($templates)->where('category', $cat)->count() }})</button>
  @endforeach
</div>

<div class="tpl-grid" id="tplGrid">
  @foreach ($templates as $t)
    @php
      $visa = \App\Support\Destinations::visaForCountry($t['country']);
      $photoSlug = \Illuminate\Support\Str::slug($t['destination']);
      $hasPhoto = file_exists(public_path("img/destinations/{$photoSlug}.jpg"));
    @endphp
    <div class="tpl-card reveal" data-cat="{{ $t['category'] }}">
      @if($hasPhoto)
        <img class="tpl-photo" src="{{ asset("img/destinations/{$photoSlug}.jpg") }}" alt="{{ $t['destination'] }}" loading="lazy">
      @endif
      <div class="tpl-body">
        <div class="tpl-top">
          <span class="cat-tag">{{ $t['category'] }}</span>
          <span class="continent-tag">{{ $t['continent'] }}</span>
        </div>
        <h3>{{ $t['destination'] }}</h3>
        <div class="place">{{ $t['country'] }} · {{ $t['region'] }}</div>
        @if($visa)
          <span class="visa-pill {{ $visa['visa_status'] }}">🛂 {{ \App\Support\Destinations::visaLabel($visa['visa_status']) }}</span>
        @endif
        <div class="tpl-meta">
          <span>🗓️ <b>{{ $t['best_season'] }}</b></span>
          <span>⏱️ <b>{{ $t['trip_length'] }}</b></span>
        </div>
        <p class="overview">{{ $t['overview'] }}</p>
        <a class="cta" href="{{ route('trips.create', ['destination' => $t['destination'] . ', ' . $t['country']]) }}">Plan this trip →</a>
      </div>
    </div>
  @endforeach
</div>

<script>
(function () {
  var tabs = document.querySelectorAll('#catTabs button');
  var cards = document.querySelectorAll('#tplGrid .tpl-card');
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.toggle('on', b === btn); });
      var cat = btn.dataset.cat;
      cards.forEach(function (c) { c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none'; });
    });
  });
})();
</script>
@endsection
