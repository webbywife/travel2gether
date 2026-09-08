@extends('layouts.site')

@section('title', 'Travel2gether — plan the trip together, not in the group chat')

@push('parallax')
  @include('partials.parallax')
@endpush

@push('styles')
<style>
  .hero{max-width:1080px; margin:0 auto; padding:48px 24px 24px; text-align:center;}
  .hero .kicker{font-family:'JetBrains Mono',monospace; font-size:12.5px; letter-spacing:0.16em; text-transform:uppercase; color:var(--pink); margin-bottom:18px;}
  .hero h1{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:clamp(34px, 6vw, 60px); line-height:1.04; letter-spacing:-0.02em; margin:0 auto 18px; max-width:14ch;}
  .hero h1 span{color:var(--pink);}
  .hero .sub{font-size:clamp(16px,2.2vw,20px); color:var(--text-dim); max-width:60ch; margin:0 auto 26px; line-height:1.6;}
  .cta-row{display:flex; gap:14px; justify-content:center; flex-wrap:wrap; margin-bottom:14px;}
  .btn-lg{font-size:16px; padding:14px 26px;}
  .trust{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-dim); letter-spacing:0.02em;}
  .trust b{color:var(--accent);}

  .section{max-width:1080px; margin:0 auto; padding:56px 24px;}
  .section h2{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:clamp(24px,3.4vw,34px); letter-spacing:-0.01em; margin:0 0 12px; text-align:center;}
  .section .section-sub{color:var(--text-dim); text-align:center; max-width:56ch; margin:0 auto 40px;}

  /* problem -> fix */
  .split{display:grid; grid-template-columns:1fr 1fr; gap:18px;}
  .split .box{border:1px solid var(--line); border-radius:16px; padding:26px; background:rgb(255 255 255 / 70%);}
  .split .box h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:0 0 12px;}
  .split .before{border-left:3px solid var(--text-dim);}
  .split .after{border-left:3px solid var(--pink); background:var(--panel);}
  .split ul{margin:0; padding-left:20px; color:var(--text-dim); font-size:14.5px;}
  .split li{margin-bottom:8px;}
  .split li:last-child{margin-bottom:0;}

  /* live preview */
  .preview-frame{
    border:2px solid var(--pink); border-radius:18px; overflow:hidden; background:var(--panel);
    box-shadow:0 20px 60px rgba(194,42,102,0.14); max-width:900px; margin:0 auto;
  }
  .preview-bar{display:flex; align-items:center; gap:8px; padding:11px 16px; background:var(--panel-2); border-bottom:1px solid var(--line);}
  .preview-bar .dot{width:10px; height:10px; border-radius:50%; background:var(--pink-light);}
  .preview-bar .addr{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-dim); margin-left:6px;}
  .preview-frame iframe{width:100%; height:560px; border:0; display:block; background:#fff;}
  .preview-note{text-align:center; color:var(--text-dim); font-size:13.5px; margin-top:16px;}
  .preview-note a{font-weight:600;}

  /* steps */
  .steps{display:grid; grid-template-columns:repeat(3,1fr); gap:18px; counter-reset:step;}
  .step{border:1px solid var(--line); border-radius:16px; padding:24px; background:rgb(255 255 255 / 70%);}
  .step::before{counter-increment:step; content:"0" counter(step); font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--pink); font-weight:700; letter-spacing:0.1em;}
  .step h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:10px 0 8px;}
  .step p{color:var(--text-dim); font-size:14.5px; margin:0;}

  /* features */
  .features{display:grid; grid-template-columns:repeat(3,1fr); gap:16px;}
  .feature{border:1px solid var(--line); border-radius:14px; padding:22px; background:var(--panel);}
  .feature .tag{font-family:'JetBrains Mono',monospace; font-size:10.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--lavender); margin-bottom:10px;}
  .feature h3{font-family:'Space Grotesk',sans-serif; font-size:16.5px; margin:0 0 7px;}
  .feature p{color:var(--text-dim); font-size:13.8px; margin:0; line-height:1.55;}

  /* freemium band */
  .band{background:var(--pink); color:#fff; border-radius:22px; padding:48px 32px; text-align:center; margin:0 24px;}
  .band-inner{max-width:1032px; margin:0 auto;}
  .band h2{color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:clamp(26px,4vw,40px); margin:0 0 12px; letter-spacing:-0.01em;}
  .band p{color:rgba(255,255,255,0.9); max-width:52ch; margin:0 auto 26px; font-size:16px;}
  .band .btn{background:#fff; color:var(--pink); border-color:#fff;}
  .band .btn:hover{background:var(--panel-2);}
  .band .fine{display:block; margin-top:16px; font-family:'JetBrains Mono',monospace; font-size:11.5px; color:rgba(255,255,255,0.75);}

  @media (max-width:760px){
    .split, .steps, .features{grid-template-columns:1fr;}
    .preview-frame iframe{height:460px;}
  }
</style>
@endpush

@section('content')
<header class="hero">
  <div class="kicker">AI-drafted · group-editable · shareable</div>
  <h1>Plan the trip <span>together</span>, not in the group chat.</h1>
  <p class="sub">
    Tell Travel2gether where you're headed, when, and who's coming. Get a full day-by-day
    itinerary back — meals, activities, cost tags, weather backups — that your whole group
    taps through and shapes together.
  </p>
  <div class="cta-row">
    <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Plan my first trip — free</a>
    @if($sampleTrip ?? null)
      <a class="btn btn-ghost btn-lg" href="{{ route('trips.show', $sampleTrip) }}">See a sample itinerary</a>
    @endif
  </div>
  <p class="trust">No card required &nbsp;·&nbsp; <b>Your first full AI itinerary is on the house</b></p>
</header>

<section class="section">
  <div class="split">
    <div class="box before">
      <h3>Planning a trip in a group chat</h3>
      <ul>
        <li>200 unread messages, three abandoned Google Docs</li>
        <li>"Did anyone actually book the restaurant?"</li>
        <li>No one remembers why you picked that hotel</li>
        <li>The one rainy-day plan lives in someone's head</li>
      </ul>
    </div>
    <div class="box after">
      <h3>Planning it on Travel2gether</h3>
      <ul>
        <li>One itinerary, every day laid out with 3+ options per slot</li>
        <li>Tap a card to lock a pick — everyone sees it, with who chose it</li>
        <li>A live budget worksheet that updates as picks change</li>
        <li>An indoor backup already attached to every outdoor stop</li>
      </ul>
    </div>
  </div>
</section>

<section class="section" style="padding-top:16px;">
  <h2>This is what you get</h2>
  <p class="section-sub">A real Travel2gether itinerary — not a screenshot. Tap the option cards, open the budget tab, scroll a day.</p>
  <div class="preview-frame">
    <div class="preview-bar">
      <span class="dot"></span><span class="dot" style="background:var(--lavender)"></span><span class="dot" style="background:var(--accent)"></span>
      <span class="addr">{{ $sampleTrip ? 'travel2gether · '.$sampleTrip->destination : 'travel2gether · sample trip' }}</span>
    </div>
    @if($sampleTrip ?? null)
      <iframe src="{{ route('trips.show', $sampleTrip) }}" title="Sample itinerary preview" loading="lazy"></iframe>
    @endif
  </div>
  @if($sampleTrip ?? null)
    <p class="preview-note"><a href="{{ route('trips.show', $sampleTrip) }}">Open the full sample in its own tab →</a></p>
  @endif
</section>

<section class="section" id="how" style="padding-top:16px;">
  <h2>Three steps to a plan</h2>
  <p class="section-sub">The first draft takes about a minute. Shaping it with your group takes as long as you want.</p>
  <div class="steps">
    <div class="step">
      <h3>Describe it</h3>
      <p>Destination, dates, who's coming, rough budget, what you're into. A sentence or two is enough.</p>
    </div>
    <div class="step">
      <h3>Get a full draft</h3>
      <p>A complete day-by-day itinerary — options per time slot at budget / mid / splurge, cost tags, weather-aware notes, and the things that could go wrong.</p>
    </div>
    <div class="step">
      <h3>Shape it together</h3>
      <p>Invite the group with a link. Everyone votes on options, swaps stops, and edits the budget. Every pick shows who made it.</p>
    </div>
  </div>
</section>

<section class="section" style="padding-top:16px;">
  <h2>Built for the messy middle of planning</h2>
  <p class="section-sub">Everything the group argues about, made concrete.</p>
  <div class="features">
    <div class="feature">
      <div class="tag">Options</div>
      <h3>3+ picks per slot</h3>
      <p>Every meal, activity, and shopping stop comes with budget, mid, and splurge choices — plus an indoor one for bad weather.</p>
    </div>
    <div class="feature">
      <div class="tag">Budget</div>
      <h3>Live worksheet</h3>
      <p>Cost ranges roll into a running total and a per-day average. Change a pick, watch the number move.</p>
    </div>
    <div class="feature">
      <div class="tag">Weather</div>
      <h3>Rainy-day backups</h3>
      <p>Forecast turns bad and the outdoor stops get flagged — with the pre-picked indoor alternative one tap away, and the cost difference shown.</p>
    </div>
    <div class="feature">
      <div class="tag">Collaboration</div>
      <h3>Share with roles</h3>
      <p>Send a link. Owners lock decisions, editors propose and vote, viewers just look. Same mental model as a shared doc.</p>
    </div>
    <div class="feature">
      <div class="tag">Honesty</div>
      <h3>Documented hiccups</h3>
      <p>Every day spells out what could go wrong — the stall that closes early, the queue that runs 45 minutes — so no one's surprised on the ground.</p>
    </div>
    <div class="feature">
      <div class="tag">Reuse</div>
      <h3>Remix any trip</h3>
      <p>Found a public itinerary you like? Duplicate it as your own starting point and change it from there.</p>
    </div>
  </div>
</section>

<section class="section" style="padding:24px 0 56px;">
  <div class="band">
    <div class="band-inner">
      <h2>Your first trip is free.</h2>
      <p>One complete AI-generated itinerary per account, no card required — enough to plan a real trip start to finish and see if it fits how your group works.</p>
      <a class="btn btn-lg" href="{{ route('register') }}">Create my free account</a>
      <span class="fine">Takes 20 seconds · email and a password, that's it</span>
    </div>
  </div>
</section>
@endsection
