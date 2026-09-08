@extends('layouts.site')

@section('title', 'Travel2gether — plan the trip together, not in the group chat')

@push('parallax')
  @include('partials.parallax')
@endpush

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;600;700&display=swap" rel="stylesheet">
<style>
  .wrapc{max-width:1120px; margin:0 auto; padding:0 24px;}

  /* hero */
  .hero{max-width:1120px; margin:0 auto; padding:72px 24px 32px; text-align:center;}
  .hero .kicker{
    display:inline-block; font-family:'JetBrains Mono',monospace; font-size:11.5px;
    letter-spacing:0.18em; text-transform:uppercase; color:var(--accent);
    border:1px solid var(--line); background:rgba(255,255,255,0.7); border-radius:999px;
    padding:7px 14px; margin-bottom:24px;
  }
  .hero h1{
    font-family:'Space Grotesk',sans-serif; font-weight:700;
    font-size:clamp(34px, 5.6vw, 58px); line-height:1.05; letter-spacing:-0.025em;
    margin:0 auto 20px; max-width:15ch; color:var(--text);
  }
  .hero .sub{font-size:clamp(16px,2.1vw,20px); color:var(--text-dim); max-width:58ch; margin:0 auto 30px; line-height:1.6;}
  .cta-row{display:flex; gap:14px; justify-content:center; flex-wrap:wrap; margin-bottom:18px;}
  .btn-lg{font-size:16px; padding:15px 28px;}
  .trust{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-dim); letter-spacing:0.02em;}
  .trust b{color:var(--accent);}

  /* sections */
  .section{max-width:1120px; margin:0 auto; padding:64px 24px;}
  .section-tight{padding-top:20px;}
  .section h2{
    font-family:'Space Grotesk',sans-serif; font-weight:700;
    font-size:clamp(25px,3.2vw,36px); letter-spacing:-0.02em; margin:0 0 12px; text-align:center;
    width:fit-content; margin-left:auto; margin-right:auto;
  }
  .section .section-sub{color:var(--text-dim); text-align:center; max-width:56ch; margin:0 auto 44px;}

  /* problem -> fix */
  .split{display:grid; grid-template-columns:1fr 1fr; gap:20px;}
  .split .box{
    position:relative; border:1px solid var(--line); border-radius:20px; padding:28px;
    background:rgba(255,255,255,0.92); box-shadow:var(--shadow-sm);
  }
  .split .box h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:0 0 14px; letter-spacing:-0.01em;}
  .split .before h3{color:var(--text-dim);}
  .split .after{background:linear-gradient(180deg, rgba(253,234,241,0.6), rgba(255,255,255,0.92));}
  .split .after::before{
    content:""; position:absolute; left:0; top:18px; bottom:18px; width:4px; border-radius:4px; background:var(--grad);
  }
  .split ul{margin:0; padding-left:20px; color:var(--text-dim); font-size:14.5px;}
  .split li{margin-bottom:9px;}
  .split li:last-child{margin-bottom:0;}

  /* live preview — a page torn from a planning notebook */
  .scrapbook{
    position:relative; max-width:940px; margin:0 auto; padding:44px 30px 30px;
    background:#FFFDF7;
    background-image:
      linear-gradient(90deg, transparent 0 54px, rgba(194,42,102,0.28) 54px 55px, transparent 55px),
      repeating-linear-gradient(#FFFDF7 0 31px, #E7DEF0 31px 32px);
    border:1px solid #EAE3D6; border-radius:4px;
    box-shadow:
      0 1px 0 rgba(255,255,255,0.7) inset,
      0 24px 50px -22px rgba(120,40,80,0.28),
      0 3px 10px rgba(36,30,35,0.06);
    transform:rotate(-0.7deg);
    transition:transform .3s ease;
  }
  .scrapbook:hover{transform:rotate(0);}
  /* torn top edge */
  .scrapbook::before{
    content:""; position:absolute; left:0; right:0; top:-6px; height:10px;
    background:
      radial-gradient(circle at 6px 8px, transparent 5px, #FFFDF7 5px) repeat-x;
    background-size:14px 10px; filter:drop-shadow(0 -1px 0 #EAE3D6);
  }
  .sb-clip{
    position:absolute; top:-16px; left:40px; width:26px; height:64px; z-index:4;
    border:3px solid #B7B2AD; border-radius:14px;
    border-bottom-color:transparent; transform:rotate(-11deg);
    box-shadow:0 2px 3px rgba(0,0,0,0.15);
  }
  .sb-clip::after{
    content:""; position:absolute; inset:6px 5px 12px 5px;
    border:3px solid #CFCAC4; border-radius:10px; border-bottom-color:transparent;
  }
  .sb-head{display:flex; flex-wrap:wrap; align-items:baseline; justify-content:space-between; gap:10px; margin:0 0 14px;}
  .sb-label{
    font-family:'Caveat',cursive; font-size:30px; font-weight:700;
    color:var(--accent); transform:rotate(-1.5deg); margin-left:8px; line-height:1;
  }
  .sb-tabs{display:flex; gap:6px;}
  .sb-tab{
    font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.04em;
    padding:5px 11px; border-radius:999px; border:1px solid var(--line); background:#fff;
    color:var(--text-dim); cursor:pointer; transition:all .15s ease;
  }
  .sb-tab:hover{border-color:var(--pink-light); color:var(--text);}
  .sb-tab.on{background:var(--grad-soft); color:#fff; border-color:transparent;}
  .sb-paste{
    position:relative; background:#fff; border:1px solid #ECE7DE; border-radius:3px;
    padding:6px; box-shadow:0 10px 26px -12px rgba(36,30,35,0.35);
    transform:rotate(0.8deg);
  }
  .sb-paste iframe{width:100%; height:560px; border:0; display:block; background:#fff; border-radius:2px;}
  .tape{
    position:absolute; width:104px; height:28px; z-index:3;
    background:linear-gradient(180deg, rgba(246,169,198,0.55), rgba(246,169,198,0.4));
    border:1px solid rgba(255,255,255,0.35);
    box-shadow:0 1px 3px rgba(36,30,35,0.12);
  }
  .tape::after{content:""; position:absolute; inset:0; background:repeating-linear-gradient(90deg, transparent 0 5px, rgba(255,255,255,0.25) 5px 6px);}
  .tape-l{top:-14px; left:24px; transform:rotate(-6deg);}
  .tape-r{top:-14px; right:24px; transform:rotate(5deg);}
  .sb-note{
    display:block; font-family:'Caveat',cursive; font-size:21px; color:var(--pink);
    transform:rotate(-1deg); margin:16px 0 0 12px;
  }
  .preview-note{text-align:center; color:var(--text-dim); font-size:13.5px; margin-top:22px;}
  .preview-note a{font-weight:600;}
  @media (max-width:820px){
    .scrapbook{padding:38px 16px 22px; background-image:repeating-linear-gradient(#FFFDF7 0 31px, #E7DEF0 31px 32px);}
    .sb-clip{left:20px;}
  }

  /* steps */
  .steps{display:grid; grid-template-columns:repeat(3,1fr); gap:20px; counter-reset:step;}
  .step{
    position:relative; border:1px solid var(--line); border-radius:20px; padding:28px 24px 24px;
    background:rgba(255,255,255,0.92); box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .step:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .step::before{
    counter-increment:step; content:counter(step, decimal-leading-zero);
    display:flex; align-items:center; justify-content:center;
    width:38px; height:38px; border-radius:12px; margin-bottom:16px;
    font-family:'JetBrains Mono',monospace; font-size:13px; font-weight:700; color:#fff;
    background:var(--grad-soft); box-shadow:0 8px 18px -8px rgba(194,42,102,0.5);
  }
  .step h3{font-family:'Space Grotesk',sans-serif; font-size:18px; margin:0 0 8px; letter-spacing:-0.01em;}
  .step p{color:var(--text-dim); font-size:14.5px; margin:0;}

  /* features */
  .features{display:grid; grid-template-columns:repeat(3,1fr); gap:18px;}
  .feature{
    border:1px solid var(--line); border-radius:18px; padding:24px;
    background:rgba(255,255,255,0.92); box-shadow:var(--shadow-sm);
    transition:transform .18s ease, box-shadow .18s ease;
  }
  .feature:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .feature .tag{
    display:inline-block; font-family:'JetBrains Mono',monospace; font-size:10.5px;
    letter-spacing:0.1em; text-transform:uppercase; font-weight:700; margin-bottom:12px;
  }
  .feature h3{font-family:'Space Grotesk',sans-serif; font-size:17px; margin:0 0 8px; letter-spacing:-0.01em;}
  .feature p{color:var(--text-dim); font-size:14px; margin:0; line-height:1.55;}

  /* freemium band */
  .band{
    position:relative; overflow:hidden; color:#fff; text-align:center;
    border-radius:28px; padding:56px 32px; margin:0 24px;
    background:linear-gradient(120deg, #C22A66 0%, #8E3A73 58%, #6E54A6 100%);
    box-shadow:0 30px 70px -28px rgba(120,40,80,0.55);
  }
  .band-inner{position:relative; max-width:1032px; margin:0 auto;}
  .band h2{color:#fff; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:clamp(27px,4vw,42px); margin:0 0 14px; letter-spacing:-0.02em;}
  .band p{color:rgba(255,255,255,0.92); max-width:52ch; margin:0 auto 28px; font-size:16px;}
  .band .btn{background:#fff; color:var(--pink); border-color:#fff; box-shadow:0 12px 30px -12px rgba(0,0,0,0.35);}
  .band .btn:hover{background:#fff; transform:translateY(-1px);}
  .band .fine{display:block; margin-top:16px; font-family:'JetBrains Mono',monospace; font-size:11.5px; color:rgba(255,255,255,0.78);}

  @media (max-width:820px){
    .split, .steps, .features{grid-template-columns:1fr;}
    .sb-paste iframe{height:460px;}
    .hero{padding-top:52px;}
  }
</style>
@endpush

@section('content')
<header class="hero">
  <div class="kicker">AI-drafted · group-editable · shareable</div>
  <h1 class="reveal">Plan the trip <span class="gtext">together</span>, not in the group chat.</h1>
  <p class="sub reveal d1">
    Tell Travel2gether where you're headed, when, and who's coming. Get a full day-by-day
    itinerary back — meals, activities, cost tags, weather backups — that your whole group
    taps through and shapes together.
  </p>
  <div class="cta-row reveal d2">
    <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Plan my first trip — free</a>
    @if($sampleTrip ?? null)
      <a class="btn btn-ghost btn-lg" href="{{ route('trips.show', $sampleTrip) }}">See a sample itinerary</a>
    @endif
  </div>
  <p class="trust reveal d3">No card required &nbsp;·&nbsp; <b>Your first full AI itinerary is on the house</b></p>
</header>

<section class="section reveal">
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
      <h3 class="gtext">Planning it on Travel2gether</h3>
      <ul>
        <li>One itinerary, every day laid out with 3+ options per slot</li>
        <li>Tap a card to lock a pick — everyone sees it, with who chose it</li>
        <li>A live budget worksheet that updates as picks change</li>
        <li>An indoor backup already attached to every outdoor stop</li>
      </ul>
    </div>
  </div>
</section>

<section class="section section-tight reveal">
  <h2 class="gtext">This is what you get</h2>
  <p class="section-sub">A real Travel2gether itinerary — not a screenshot. Tap the option cards, open the budget tab, scroll a day.</p>
  <div class="scrapbook" id="scrapbook">
    <div class="sb-clip" aria-hidden="true"></div>
    <div class="sb-head">
      <span class="sb-label" id="sbLabel">{{ $sampleTrip ? strtolower($sampleTrip->destination) : 'our trip' }} — plan ✎</span>
      @if(($samples ?? collect())->count() > 1)
        <div class="sb-tabs">
          @foreach($samples as $s)
            <button type="button" class="sb-tab {{ $loop->first ? 'on' : '' }}"
                    data-src="{{ route('trips.show', $s) }}"
                    data-label="{{ strtolower($s->destination) }} — plan ✎"
                    data-href="{{ route('trips.show', $s) }}">{{ $s->title }}</button>
          @endforeach
        </div>
      @endif
    </div>
    @if($sampleTrip ?? null)
      <div class="sb-paste">
        <div class="tape tape-l" aria-hidden="true"></div>
        <div class="tape tape-r" aria-hidden="true"></div>
        <iframe id="sbFrame" src="{{ route('trips.show', $sampleTrip) }}" title="Sample itinerary preview" loading="lazy"></iframe>
      </div>
      <span class="sb-note">↑ tap a card — it remembers your pick</span>
    @endif
  </div>
  @if($sampleTrip ?? null)
    <p class="preview-note"><a id="sbOpen" href="{{ route('trips.show', $sampleTrip) }}">Open the full sample in its own tab →</a></p>
  @endif
  @if(($samples ?? collect())->count() > 1)
  <script>
  (function () {
    var frame = document.getElementById('sbFrame'), label = document.getElementById('sbLabel'), open = document.getElementById('sbOpen');
    document.querySelectorAll('.sb-tab').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.sb-tab').forEach(function (b) { b.classList.toggle('on', b === btn); });
        frame.src = btn.dataset.src;
        label.textContent = btn.dataset.label;
        if (open) open.href = btn.dataset.href;
      });
    });
  })();
  </script>
  @endif
</section>

<section class="section section-tight reveal" id="how">
  <h2 class="gtext">Three steps to a plan</h2>
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

<section class="section section-tight reveal">
  <h2 class="gtext">Built for the messy middle of planning</h2>
  <p class="section-sub">Everything the group argues about, made concrete.</p>
  <div class="features">
    <div class="feature">
      <div class="tag gtext">Options</div>
      <h3>3+ picks per slot</h3>
      <p>Every meal, activity, and shopping stop comes with budget, mid, and splurge choices — plus an indoor one for bad weather.</p>
    </div>
    <div class="feature">
      <div class="tag gtext">Budget</div>
      <h3>Live worksheet</h3>
      <p>Cost ranges roll into a running total and a per-day average. Change a pick, watch the number move.</p>
    </div>
    <div class="feature">
      <div class="tag gtext">Weather</div>
      <h3>Rainy-day backups</h3>
      <p>Forecast turns bad and the outdoor stops get flagged — with the pre-picked indoor alternative one tap away, and the cost difference shown.</p>
    </div>
    <div class="feature">
      <div class="tag gtext">Collaboration</div>
      <h3>Share with roles</h3>
      <p>Send a link. Owners lock decisions, editors propose and vote, viewers just look. Same mental model as a shared doc.</p>
    </div>
    <div class="feature">
      <div class="tag gtext">Honesty</div>
      <h3>Documented hiccups</h3>
      <p>Every day spells out what could go wrong — the stall that closes early, the queue that runs 45 minutes — so no one's surprised on the ground.</p>
    </div>
    <div class="feature">
      <div class="tag gtext">Reuse</div>
      <h3>Remix any trip</h3>
      <p>Found a public itinerary you like? Duplicate it as your own starting point and change it from there.</p>
    </div>
  </div>
</section>

<section class="section section-tight reveal" style="padding-bottom:64px;">
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
