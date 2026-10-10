@extends('layouts.site')

@php
  use App\Support\Destinations;
  use App\Support\Gallery;
  use Illuminate\Support\Str;

  $total = $groups->flatten()->count();
  $regionLabel = ['Asia' => 'Asia', 'USA' => 'USA', 'Europe' => 'Europe'];
@endphp

@section('title', "{$total} sample itineraries you can copy — Japan, Korea, Singapore, the USA and Europe · Travel2gether")
@section('meta_description', 'Day-by-day sample travel itineraries for Tokyo, Seoul, Singapore, Kyoto, Osaka, New York, the Grand Canyon, Paris and Rome — with options for every time slot, a budget, rain backups and visa notes for a Philippine passport.')

@push('styles')
<style>
  .sx-hero{max-width:900px; margin:0 auto; padding:48px 24px 8px; text-align:center;}
  .sx-hero h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,42px); margin:0 0 12px; letter-spacing:-0.02em;}
  .sx-hero p{color:var(--text-dim); max-width:62ch; margin:0 auto;}
  .sx-tabs{display:flex; flex-wrap:wrap; justify-content:center; gap:8px; margin:26px auto 30px; padding:0 24px;}
  .sx-tabs button{font-family:'JetBrains Mono',monospace; font-size:11.5px; letter-spacing:0.03em; padding:8px 15px; border-radius:999px; border:1px solid var(--line); background:#fff; color:var(--text-dim); cursor:pointer;}
  .sx-tabs button:hover{border-color:var(--pink-light); color:var(--text);}
  .sx-tabs button.on{background:var(--grad-soft); color:#fff; border-color:transparent;}

  .sx-region{max-width:1120px; margin:0 auto; padding:0 24px 34px;}
  .sx-region h2{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin:0 0 14px;}
  .sx-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(310px,1fr)); gap:18px;}
  .sx-card{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.95); box-shadow:var(--shadow-sm); overflow:hidden; display:flex; flex-direction:column; transition:transform .16s ease, box-shadow .16s ease;}
  .sx-card:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .sx-photo{position:relative; aspect-ratio:16/10; background:var(--grad); display:block; overflow:hidden;}
  .sx-photo img{position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block;}
  .sx-photo .sx-days{position:absolute; left:12px; top:12px; font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; letter-spacing:0.05em; text-transform:uppercase; background:rgba(255,255,255,0.94); color:var(--accent); border-radius:999px; padding:5px 11px;}
  .sx-photo .sx-mine{position:absolute; right:12px; top:12px; font-size:11px; font-weight:600; background:rgba(36,30,35,0.72); color:#fff; border-radius:999px; padding:5px 10px;}
  .sx-body{padding:18px 20px 20px; display:flex; flex-direction:column; flex:1;}
  .sx-body h3{font-family:'Space Grotesk',sans-serif; font-size:20px; margin:0 0 3px; letter-spacing:-0.01em;}
  .sx-body h3 a{color:inherit; text-decoration:none;}
  .sx-meta{font-size:12.5px; color:var(--text-dim); margin-bottom:10px;}
  .sx-pills{display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px;}
  .sx-pill{font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; letter-spacing:0.03em; text-transform:uppercase; border-radius:999px; padding:3px 9px; background:var(--panel-2); color:var(--accent);}
  .sx-pill.visa_free{background:rgba(59,167,118,0.12); color:#2f6d54;}
  .sx-pill.evisa{background:rgba(113,86,168,0.12); color:#5a4488;}
  .sx-pill.required{background:rgba(194,42,102,0.1); color:var(--accent);}
  .sx-body p{font-size:13.5px; color:var(--text-dim); line-height:1.55; margin:0 0 16px; flex:1;}
  .sx-actions{display:flex; flex-direction:column; align-items:flex-start; gap:10px;}
  .sx-actions .btn{padding:9px 16px; font-size:13.5px;}
  .sx-actions .plan{font-size:13px; font-weight:600; text-decoration:none;}
  .sx-foot{max-width:760px; margin:10px auto 64px; padding:0 24px; text-align:center; color:var(--text-dim); font-size:13.5px;}
  @media (max-width:560px){ .sx-grid{grid-template-columns:1fr;} .sx-tabs{flex-wrap:nowrap; overflow-x:auto; justify-content:flex-start;} .sx-tabs button{flex:0 0 auto;} }
</style>
@endpush

@section('content')
<header class="sx-hero">
  <h1><span class="gtext">Sample trips</span> you can copy</h1>
  <p>{{ $total }} complete day-by-day plans — every time slot has a cheaper and a fancier option, every day lists what could go wrong, and there's a budget for a group. Open one, tap through it, then plan your own.</p>
</header>

<div class="sx-tabs" id="sxTabs">
  <button type="button" class="on" data-region="all">All ({{ $total }})</button>
  @foreach ($groups as $region => $trips)
    <button type="button" data-region="{{ $region }}">{{ $regionLabel[$region] ?? $region }} ({{ $trips->count() }})</button>
  @endforeach
</div>

@foreach ($groups as $region => $trips)
  <section class="sx-region" data-region="{{ $region }}">
    <h2>{{ $regionLabel[$region] ?? $region }}</h2>
    <div class="sx-grid">
      @foreach ($trips as $t)
        @php
          $city = Str::before($t->destination, ',');
          $country = trim(Str::afterLast($t->destination, ','));
          $slug = Str::slug($city);
          $cover = file_exists(public_path("img/destinations/{$slug}.jpg")) ? asset("img/destinations/{$slug}.jpg") : Gallery::cover($slug);
          $mine = Gallery::place($slug)['count'] ?? 0;
          $visa = Destinations::visaForCountry($country);
        @endphp
        <article class="sx-card">
          <a class="sx-photo" href="{{ route('trips.show', $t) }}" aria-label="Open the {{ $t->title }} plan">
            @if ($cover)<img src="{{ $cover }}" alt="{{ $city }}" loading="lazy" decoding="async">@endif
            <span class="sx-days">{{ $t->days_count }} days · {{ $t->start_date->format('M Y') }}</span>
            @if ($mine)<span class="sx-mine">📷 {{ $mine }} of my photos</span>@endif
          </a>
          <div class="sx-body">
            <h3><a href="{{ route('trips.show', $t) }}">{{ $t->title }}</a></h3>
            <div class="sx-meta">{{ $t->destination }}@if($t->tagline) · {{ $t->tagline }}@endif</div>
            <div class="sx-pills">
              @if ($visa)<span class="sx-pill {{ $visa['visa_status'] }}">🛂 {{ Destinations::visaLabel($visa['visa_status']) }}</span>@endif
              <span class="sx-pill">{{ $t->origin_label }}</span>
            </div>
            <p>{{ Str::limit($t->subhead, 170) }}</p>
            <div class="sx-actions">
              <a class="btn btn-primary" href="{{ route('trips.show', $t) }}">Open the plan</a>
              <a class="plan" href="{{ route('trips.create', ['destination' => $t->destination]) }}">Plan my own {{ $city }} trip →</a>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </section>
@endforeach

<p class="sx-foot">Prices, hours and entry rules were checked when each plan was written — they change, so confirm before you book. Visa notes are for a Philippine passport.</p>

<script>
(function () {
  var tabs = document.querySelectorAll('#sxTabs button'), regions = document.querySelectorAll('.sx-region');
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.toggle('on', b === btn); });
      regions.forEach(function (r) { r.style.display = (btn.dataset.region === 'all' || r.dataset.region === btn.dataset.region) ? '' : 'none'; });
    });
  });
})();
</script>
@endsection
