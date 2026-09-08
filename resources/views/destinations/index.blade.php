@extends('layouts.site')

@section('title', 'Get inspired · Travel2gether')
@section('meta_description', 'The world\'s most-visited countries, their standout cities, and what a Philippine passport needs to get in.')

@push('styles')
<style>
  .dest-hero{max-width:900px; margin:0 auto; padding:48px 24px 8px; text-align:center;}
  .dest-hero h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,42px); margin:0 0 12px; letter-spacing:-0.02em;}
  .dest-hero p{color:var(--text-dim); max-width:56ch; margin:0 auto;}
  .visa-key{display:flex; justify-content:center; gap:14px; flex-wrap:wrap; margin:22px 0 6px; font-size:12.5px;}
  .visa-key span{display:inline-flex; align-items:center; gap:6px; color:var(--text-dim);}
  .visa-key i{width:9px; height:9px; border-radius:50%; display:inline-block;}
  .visa-note{max-width:820px; margin:18px auto 40px; padding:12px 16px; border:1px solid var(--line); background:var(--panel); border-radius:12px; font-size:12.5px; color:var(--text-dim); text-align:center;}

  .dest-grid{max-width:1120px; margin:0 auto; padding:0 24px 64px; display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;}
  .dest-card{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.92); box-shadow:var(--shadow-sm); padding:20px; transition:transform .16s ease, box-shadow .16s ease;}
  .dest-card:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .dest-card .top{display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:4px;}
  .dest-card .flag{font-size:26px; line-height:1;}
  .dest-card h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:0; letter-spacing:-0.01em;}
  .dest-card .region{font-family:'JetBrains Mono',monospace; font-size:10.5px; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-dim); margin:1px 0 10px;}
  .visa-pill{display:inline-flex; align-items:center; gap:5px; font-family:'JetBrains Mono',monospace; font-size:10.5px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; border-radius:999px; padding:4px 10px;}
  .visa-pill.free{background:rgba(59,167,118,0.12); color:#2f6d54;}
  .visa-pill.evisa{background:rgba(113,86,168,0.12); color:#5a4488;}
  .visa-pill.required{background:rgba(194,42,102,0.1); color:var(--accent);}
  .dest-card .note{font-size:12px; color:var(--text-dim); margin:8px 0 12px; line-height:1.5;}
  .city-chips{display:flex; flex-wrap:wrap; gap:6px;}
  .city-chips a{font-size:12.5px; text-decoration:none; color:var(--text); border:1px solid var(--line); border-radius:999px; padding:5px 11px; background:#fff;}
  .city-chips a:hover{border-color:var(--pink-light); background:var(--panel-2); color:var(--pink);}
  @media (max-width:560px){ .dest-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<header class="dest-hero reveal">
  <h1><span class="gtext">Get inspired</span></h1>
  <p>The world's most-visited countries and the cities worth building a trip around — tap a city to start planning it. Visa notes are for a <strong>Philippine passport</strong>, since that's the trip most people here are flying from.</p>
  <div class="visa-key">
    <span><i style="background:#3BA776"></i> Visa-free</span>
    <span><i style="background:#7156A8"></i> e-Visa</span>
    <span><i style="background:#C22A66"></i> Visa required</span>
  </div>
</header>

<p class="visa-note">Visa rules change and have exceptions (transit rules, accredited-agency schemes, holding a qualifying third-country visa, and more) — treat these as a starting point and confirm with the destination's embassy or official immigration site before booking anything.</p>

<div class="dest-grid">
  @foreach ($destinations as $d)
    <div class="dest-card reveal">
      <div class="top">
        <div><span class="flag">{{ $d['flag'] }}</span></div>
        <span class="visa-pill {{ $d['visa_status'] === 'visa_free' ? 'free' : $d['visa_status'] }}">
          {{ \App\Support\Destinations::visaLabel($d['visa_status']) }}
        </span>
      </div>
      <h3>{{ $d['country'] }}</h3>
      <div class="region">{{ $d['region'] }}</div>
      <p class="note">{{ $d['visa_note'] }}</p>
      <div class="city-chips">
        @foreach ($d['cities'] as $city)
          <a href="{{ route('trips.create', ['destination' => $city . ', ' . $d['country']]) }}">{{ $city }}</a>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
@endsection
