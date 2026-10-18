@extends('layouts.site')

@section('title', 'Travel2gether — turn planning into the best part of the trip, together')
@push('head')
<script type="application/ld+json">{!! json_encode([
    ["\x40context" => 'https://schema.org', '@type' => 'WebSite', 'name' => 'Travel2gether', 'url' => url('/')],
    ["\x40context" => 'https://schema.org', '@type' => 'Organization', 'name' => 'Travel2gether', 'url' => url('/'),
     'logo' => asset('android-chrome-512x512.png'), 'sameAs' => ['https://www.instagram.com/traveleyz', 'https://www.tiktok.com/@traveleyz']],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@push('styles')
<style>
  .page{overflow-x:clip;}

  /* ---------- hero ---------- */
  .hero{position:relative; text-align:center; padding:clamp(4.5rem, 3rem + 6vw, 8.5rem) 16px 0;}
  .hero .chip{margin-bottom:28px;}
  .hero h1{font-size:var(--fs-h1); line-height:.98; margin:0 auto 26px; max-width:13ch; color:var(--ink);}
  .hero h1 .gtext{padding-right:.04em;}
  .hero .lead{font-size:var(--fs-lead); color:var(--text-dim); max-width:46ch; margin:0 auto 34px; line-height:1.5;}
  .cta-row{display:flex; gap:14px; justify-content:center; flex-wrap:wrap;}
  .trust{margin:22px 0 0; font-family:var(--f-mono); font-size:.75rem; letter-spacing:.06em; text-transform:uppercase; color:var(--text-dim);}
  .trust b{color:var(--pink); font-weight:500;}
  @media (max-width:520px){ .cta-row{flex-direction:column; align-items:stretch; max-width:320px; margin:0 auto;} }

  /* stage: photo band with floating product cards */
  .stage{position:relative; max-width:1320px; margin:clamp(3rem,2rem + 4vw,5.5rem) auto 0; padding:0 24px;}
  .stage-photo{position:relative; height:clamp(320px, 22rem + 18vw, 680px); overflow:hidden; border-radius:var(--r); background:var(--paper-2);}
  .stage-photo img{position:absolute; inset:-12% 0; width:100%; height:124%; object-fit:cover; object-position:50% 68%; will-change:transform;}
  .stage-photo::after{content:""; position:absolute; inset:0; background:linear-gradient(180deg, rgba(36,30,35,0) 55%, rgba(36,30,35,.45));}
  .stage-cap{position:absolute; left:20px; bottom:18px; z-index:2; color:#fff; font-family:var(--f-mono); font-size:.75rem; letter-spacing:.08em; text-transform:uppercase;}
  .route{position:absolute; inset:0; z-index:1; width:100%; height:100%; pointer-events:none;}
  .route path{fill:none; stroke:#fff; stroke-width:2; stroke-dasharray:2 9; stroke-linecap:round; opacity:.9;}
  .route .pin{fill:var(--pink); stroke:#fff; stroke-width:3;}
  .route .draw{stroke-dasharray:1400; stroke-dashoffset:1400; transition:stroke-dashoffset 2.6s var(--ease-out) .4s; stroke:#fff; stroke-width:2; opacity:.9;}
  .stage.is-visible .route .draw{stroke-dashoffset:0;}
  .route-mask path{stroke-dasharray:none;}

  .float{position:absolute; z-index:3; background:#fff; border-radius:var(--r); padding:14px 16px; width:min(270px, 62vw); text-align:left;
    box-shadow:0 0 0 .5px var(--hair), 0 24px 50px -24px rgba(36,30,35,.45); font-size:.875rem; line-height:1.35;
    opacity:0; transform:translateY(24px) scale(.96); transition:opacity .9s var(--ease-out), transform .9s var(--ease-out);}
  .stage.is-visible .float{opacity:1; transform:none;}
  .stage.is-visible .f2{transition-delay:.35s;} .stage.is-visible .f3{transition-delay:.7s;}
  .float .k{display:flex; align-items:center; gap:8px; font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.08em; text-transform:uppercase; color:var(--text-dim); margin-bottom:8px;}
  .float .k i{width:8px; height:8px; border-radius:50%; background:var(--pink); font-style:normal;}
  .float b{font-weight:600;}
  .float .row{display:flex; justify-content:space-between; gap:10px; align-items:baseline;}
  .float .delta{font-family:var(--f-mono); font-variant-numeric:tabular-nums; color:#2f7d58; font-size:.8125rem;}
  .float .av{display:inline-flex; width:22px; height:22px; border-radius:50%; align-items:center; justify-content:center; color:#fff; font-size:.625rem; font-weight:600; background:var(--accent); margin-right:-6px; border:2px solid #fff;}
  .float .av:nth-child(2){background:var(--lavender);} .float .av:nth-child(3){background:var(--pink);}
  .float .bar{height:4px; background:var(--paper-2); border-radius:2px; margin-top:10px; overflow:hidden;}
  .float .bar span{display:block; height:100%; width:68%; background:var(--grad); transform-origin:left; transform:scaleX(0); transition:transform 1.6s var(--ease-out) 1.2s;}
  .stage.is-visible .float .bar span{transform:scaleX(1);}
  .f1{left:-6px; top:12%;} .f2{right:-6px; top:30%;} .f3{left:12%; bottom:-34px;}
  .float .bob{animation:t2g-bob2 6s ease-in-out infinite;}
  @keyframes t2g-bob2{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
  @media (max-width:760px){ .f1{left:4px; top:-30px;} .f2{right:4px; top:auto; bottom:28%;} .f3{left:auto; right:12px; bottom:-40px;} .float{padding:11px 13px; font-size:.8125rem;} }

  /* ---------- marquee ---------- */
  .ribbon{padding:clamp(4.5rem,3rem + 3vw,6.5rem) 0 0;}
  .ribbon-head{display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px;}
  .ribbon-head p{margin:0; color:var(--text-dim); font-size:var(--fs-sm); max-width:60ch;}
  .pause{background:none; border:0; font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.08em; text-transform:uppercase; color:var(--text-dim); cursor:pointer; padding:6px 0;}
  .pause:hover{color:var(--pink);}
  .shot{position:relative; flex:0 0 auto; width:clamp(180px, 12rem + 6vw, 300px); aspect-ratio:4/5; overflow:hidden; border-radius:var(--r); background:var(--paper-2); display:block;}
  .shot img{width:100%; height:100%; object-fit:cover; display:block; transition:transform .8s var(--ease-out);}
  .shot:hover img{transform:scale(1.06);}
  .shot span{position:absolute; left:10px; bottom:10px; background:#fff; color:var(--ink); font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.06em; text-transform:uppercase; padding:5px 8px; border-radius:2px;}
  .place{flex:0 0 auto; font-size:clamp(2rem, 1.2rem + 3vw, 4rem); letter-spacing:-.04em; color:var(--ink); text-decoration:none; padding:0 8px; display:flex; align-items:center; gap:24px;}
  .place::after{content:"✦"; font-size:.4em; color:var(--pink);}

  /* ---------- section heads ---------- */
  .sec{padding:var(--space-section) 0;}
  .sec-head{margin-bottom:clamp(2.5rem, 1.5rem + 3vw, 4.5rem);}
  .gtext{background:var(--grad); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;}

  /* ---------- before / after ---------- */
  .versus{display:grid; grid-template-columns:1fr 1fr; border-top:.5px solid var(--hair); border-bottom:.5px solid var(--hair);}
  .versus > div{padding:clamp(1.5rem,1rem + 2vw,3rem) clamp(1rem,.5rem + 2vw,2.5rem);}
  .versus > div + div{border-left:.5px solid var(--hair); background:#fff;}
  .versus h3{font-size:var(--fs-h3); margin:16px 0 22px;}
  .versus ul{list-style:none; margin:0; padding:0;}
  .versus li{padding:14px 0; border-top:.5px solid var(--line); display:flex; gap:14px; align-items:baseline;}
  .versus li .n{font-family:var(--f-mono); font-size:.75rem; color:var(--text-dim); flex-shrink:0;}
  .before li .t{color:var(--text-dim); text-decoration:line-through; text-decoration-thickness:1px; text-decoration-color:transparent; transition:text-decoration-color .6s ease; transition-delay:calc(.5s + var(--i,0) * 160ms);}
  .before.is-visible li .t{text-decoration-color:var(--pink);}
  .after li .n{color:var(--pink);}
  @media (max-width:760px){ .versus{grid-template-columns:1fr;} .versus > div + div{border-left:0; border-top:.5px solid var(--hair);} }

  /* ---------- benefits grid ---------- */
  .grid6{display:grid; grid-template-columns:repeat(3,1fr); border-top:.5px solid var(--hair); border-left:.5px solid var(--hair);}
  .cell{border-right:.5px solid var(--hair); border-bottom:.5px solid var(--hair); padding:clamp(1.5rem,1rem + 1.5vw,2.5rem); min-height:260px; display:flex; flex-direction:column; background:transparent; transition:background-color .3s ease;}
  .cell:hover{background:#fff;}
  .cell .ix{font-family:var(--f-mono); font-size:.75rem; color:var(--text-dim); letter-spacing:.06em; display:flex; justify-content:space-between;}
  .cell .ix em{font-style:normal; color:var(--pink); text-transform:uppercase; letter-spacing:.1em;}
  .cell h3{font-size:var(--fs-h3); margin:auto 0 10px; padding-top:48px; line-height:1.15;}
  .cell p{margin:0; color:var(--text-dim); font-size:var(--fs-sm); line-height:1.55;}
  .cell .ico{width:40px; height:40px; margin-top:22px; color:var(--pink); transition:transform .5s var(--ease-out);}
  .cell:hover .ico{transform:translateY(-4px) rotate(-6deg);}
  @media (max-width:960px){ .grid6{grid-template-columns:1fr 1fr;} }
  @media (max-width:600px){ .grid6{grid-template-columns:1fr;} .cell{min-height:0;} .cell h3{padding-top:28px;} }

  /* ---------- product tabs ---------- */
  .prod{background:var(--ink); color:#fff;}
  .prod .eyebrow p{color:rgba(255,255,255,.7);}
  .prod .eyebrow h2{color:#fff;}
  .tablist{display:flex; justify-content:center; gap:0; margin:0 auto clamp(2rem,1rem + 2vw,3.5rem); border:.5px solid rgba(255,255,255,.22); border-radius:var(--r); width:fit-content; max-width:100%; overflow-x:auto;}
  .tab{position:relative; background:none; border:0; color:rgba(255,255,255,.6); font:500 .8125rem/1 var(--f-sans); letter-spacing:.06em; text-transform:uppercase; padding:16px 22px; cursor:pointer; white-space:nowrap; overflow:hidden;}
  .tab + .tab{border-left:.5px solid rgba(255,255,255,.22);}
  .tab[aria-selected="true"]{color:#fff; background:rgba(255,255,255,.06);}
  .tab::after{content:""; position:absolute; left:0; bottom:0; height:2px; width:100%; background:var(--pink); transform:scaleX(0); transform-origin:left;}
  .tab[aria-selected="true"]::after{transform:scaleX(1);}
  .tab.run::after{animation:t2g-fill var(--dur) linear forwards;}
  @keyframes t2g-fill{from{transform:scaleX(0)}to{transform:scaleX(1)}}
  [data-tabs].is-paused .tab.run::after{animation-play-state:paused;}
  .panel{display:grid; grid-template-columns:1fr 1.1fr; gap:clamp(2rem,1rem + 4vw,5rem); align-items:center;}
  .panel[hidden]{display:none;}
  .panel.is-on .p-copy > *, .panel.is-on .mock{animation:t2g-in .8s var(--ease-out) both;}
  .panel.is-on .p-copy > *:nth-child(2){animation-delay:.06s;} .panel.is-on .p-copy > *:nth-child(3){animation-delay:.12s;} .panel.is-on .p-copy > *:nth-child(4){animation-delay:.18s;}
  .panel.is-on .mock{animation-delay:.1s;}
  @keyframes t2g-in{from{opacity:0; transform:translateY(18px)}to{opacity:1; transform:none}}
  .p-copy h3{font-size:var(--fs-h2); line-height:1.02; margin:18px 0 18px; color:#fff;}
  .p-copy p{color:rgba(255,255,255,.72); font-size:var(--fs-lead); line-height:1.5; margin:0 0 24px;}
  .p-copy ul{list-style:none; margin:0 0 30px; padding:0;}
  .p-copy li{padding:12px 0 12px 26px; border-top:.5px solid rgba(255,255,255,.18); position:relative; color:rgba(255,255,255,.9);}
  .p-copy li::before{content:""; position:absolute; left:0; top:21px; width:10px; height:1px; background:var(--pink-light);}
  .tabs-foot{text-align:center; margin-top:28px;}
  .tabs-foot .pause{color:rgba(255,255,255,.55);}
  .mock{background:var(--paper); color:var(--ink); border-radius:var(--r); padding:clamp(16px,1rem + 1vw,28px); box-shadow:0 40px 80px -40px rgba(0,0,0,.6);}
  .mock .m-k{font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.08em; text-transform:uppercase; color:var(--text-dim); display:flex; justify-content:space-between; margin-bottom:14px;}
  .opt{display:flex; justify-content:space-between; align-items:center; gap:12px; background:#fff; border-radius:var(--r); padding:13px 14px; margin-bottom:8px; box-shadow:0 0 0 .5px var(--hair); font-size:.9375rem;}
  .opt small{display:block; color:var(--text-dim); font-size:.75rem; font-family:var(--f-mono); letter-spacing:.04em;}
  .opt .tier{font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.06em; text-transform:uppercase; padding:4px 7px; border-radius:2px; background:var(--paper-2); color:var(--accent); white-space:nowrap;}
  .opt.on{box-shadow:0 0 0 1.5px var(--pink); }
  .opt.on .tier{background:var(--pink); color:#fff;}
  .panel.is-on .opt.on{animation:t2g-pick .6s var(--ease-out) .9s both;}
  @keyframes t2g-pick{0%{box-shadow:0 0 0 .5px var(--hair)}100%{box-shadow:0 0 0 1.5px var(--pink)}}
  .who{display:flex; align-items:center; gap:10px; padding:12px 0; border-top:.5px solid var(--line); font-size:.9375rem;}
  .who .av{width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.75rem; font-weight:600; background:var(--accent);}
  .who .role{margin-left:auto; font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.06em; text-transform:uppercase; color:var(--text-dim);}
  .bars{display:grid; gap:10px;}
  .bars .b{display:grid; grid-template-columns:110px 1fr 72px; gap:10px; align-items:center; font-size:.875rem;}
  .bars .b i{display:block; height:10px; border-radius:2px; background:var(--c); transform-origin:left; transform:scaleX(0);}
  .panel.is-on .bars .b i{animation:t2g-grow 1.1s var(--ease-out) both; animation-delay:calc(.25s + var(--n) * 90ms);}
  @keyframes t2g-grow{to{transform:scaleX(var(--w));}}
  .bars .b span:last-child{font-family:var(--f-mono); font-variant-numeric:tabular-nums; text-align:right; font-size:.8125rem;}
  .total{display:flex; justify-content:space-between; align-items:baseline; margin-top:16px; padding-top:14px; border-top:.5px solid var(--hair);}
  .total b{font-size:1.75rem; font-weight:500; letter-spacing:-.03em; font-variant-numeric:tabular-nums;}
  .wx{display:grid; grid-template-columns:repeat(6,1fr); gap:6px; margin-bottom:14px;}
  .wx div{background:#fff; border-radius:var(--r); padding:10px 4px; text-align:center; font-family:var(--f-mono); font-size:.6875rem; box-shadow:0 0 0 .5px var(--hair);}
  .wx div b{display:block; font-size:1.125rem; margin:4px 0; font-family:var(--f-sans);}
  .wx .rain{background:var(--panel-2); box-shadow:0 0 0 1px var(--pink-light);}
  .swap{display:flex; align-items:center; gap:10px;}
  .swap .opt{flex:1; margin:0;}
  .swap .opt:first-child{opacity:.55; text-decoration:line-through; text-decoration-color:var(--pink);}
  .swap .arrow{color:var(--pink); font-size:1.25rem;}
  @media (max-width:860px){ .panel{grid-template-columns:1fr;} .tab{padding:14px 14px;} }

  /* ---------- hiccups + live preview ---------- */
  .quote-sec{display:grid; grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr); border-top:.5px solid var(--hair); border-bottom:.5px solid var(--hair);}
  .quote-sec article{padding:clamp(2rem,1rem + 3vw,5rem) clamp(1rem,.5rem + 3vw,4rem) clamp(2rem,1rem + 3vw,5rem) 0; display:flex; flex-direction:column; gap:28px;}
  .q-wrap{display:flex; gap:28px; flex:1;}
  .q-rail{display:flex; flex-direction:column; align-items:center; flex-shrink:0;}
  .q-rail span{font-size:4.5rem; line-height:.7; color:var(--pink); height:36px;}
  .q-rail i{flex:1; width:.5px; background:var(--hair); margin-top:10px;}
  .q-stack{display:grid; flex:1;}
  .q-stack blockquote{grid-area:1/1; margin:0; display:flex; flex-direction:column; gap:20px; transition:opacity .5s ease .25s, transform .7s var(--ease-out) .25s;}
  .q-stack blockquote[aria-hidden="true"]{opacity:0; transform:translateY(12px); pointer-events:none; transition:opacity .2s ease, transform .2s ease;}
  .q-stack p{font-size:clamp(1.5rem, 1.1rem + 1.6vw, 2.625rem); line-height:1.12; letter-spacing:-.025em; margin:0;}
  .q-stack footer{font-family:var(--f-mono); font-size:.75rem; letter-spacing:.08em; text-transform:uppercase; color:var(--text-dim); margin-top:auto;}
  .q-dots{display:flex; gap:8px; align-items:center;}
  .q-dots button{width:28px; height:28px; border:0; background:none; cursor:pointer; padding:0; display:flex; align-items:center;}
  .q-dots button::before{content:""; display:block; width:100%; height:2px; background:var(--hair); transition:background .2s;}
  .q-dots button[aria-pressed="true"]::before{background:var(--pink);}
  .live{border-left:.5px solid var(--hair); background:#fff; padding:clamp(1rem,.5rem + 2vw,2.5rem); display:flex; flex-direction:column; gap:16px;}
  .live-head{display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;}
  .sb-tabs{display:flex; flex-wrap:wrap; gap:6px;}
  .sb-tab{font-family:var(--f-mono); font-size:.6875rem; letter-spacing:.06em; text-transform:uppercase; padding:7px 10px; border-radius:2px; border:0; background:var(--paper); color:var(--text-dim); cursor:pointer; transition:background .15s, color .15s;}
  .sb-tab:hover{color:var(--ink);}
  .sb-tab.on{background:var(--ink); color:#fff;}
  .live iframe{width:100%; height:clamp(460px, 30rem + 6vw, 640px); border:0; display:block; border-radius:var(--r); box-shadow:0 0 0 .5px var(--hair); background:var(--paper);}
  .live-note{font-size:var(--fs-sm); color:var(--text-dim); display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;}
  .live-note a{color:var(--pink); font-weight:500;}
  @media (max-width:960px){ .quote-sec{grid-template-columns:1fr;} .quote-sec article{padding-right:0;} .live{border-left:0; border-top:.5px solid var(--hair); margin:0 -24px; } }

  /* ---------- steps + stats ---------- */
  .steps{display:grid; grid-template-columns:repeat(3,1fr); border-top:.5px solid var(--hair);}
  .stp{padding:28px clamp(1rem,.5rem + 1.5vw,2rem) 0 0; position:relative;}
  .stp + .stp{padding-left:clamp(1rem,.5rem + 1.5vw,2rem); border-left:.5px solid var(--hair);}
  .stp .big{font-size:clamp(3.5rem, 2rem + 5vw, 7rem); letter-spacing:-.06em; line-height:.9; color:transparent; -webkit-text-stroke:1px var(--accent); display:block; margin-bottom:28px; transition:color .5s ease;}
  .stp:hover .big{color:var(--pink); -webkit-text-stroke-color:var(--pink);}
  .stp h3{font-size:var(--fs-h3); margin:0 0 10px;}
  .stp p{color:var(--text-dim); margin:0; font-size:var(--fs-sm); max-width:36ch;}
  .stp::before{content:""; position:absolute; left:0; right:0; top:-1px; height:2px; background:var(--pink); transform:scaleX(0); transform-origin:left; transition:transform 1s var(--ease-out);}
  .steps.is-visible .stp::before{transform:scaleX(1);}
  .steps.is-visible .stp:nth-child(2)::before{transition-delay:.4s;} .steps.is-visible .stp:nth-child(3)::before{transition-delay:.8s;}
  @media (max-width:760px){ .steps{grid-template-columns:1fr;} .stp + .stp{padding-left:0; border-left:0; border-top:.5px solid var(--hair); margin-top:28px;} .stp::before{display:none;} }
  .stats{display:grid; grid-template-columns:repeat(4,1fr); margin-top:clamp(3rem,2rem + 3vw,5rem); border-top:.5px solid var(--hair); border-bottom:.5px solid var(--hair);}
  .stat{padding:28px 20px 26px 0;}
  .stat + .stat{padding-left:20px; border-left:.5px solid var(--hair);}
  .stat b{display:block; font-size:clamp(2.25rem, 1.5rem + 2.5vw, 4rem); font-weight:500; letter-spacing:-.05em; line-height:1; font-variant-numeric:tabular-nums;}
  .stat span{font-family:var(--f-mono); font-size:.75rem; letter-spacing:.06em; text-transform:uppercase; color:var(--text-dim);}
  @media (max-width:760px){ .stats{grid-template-columns:1fr 1fr;} .stat:nth-child(3){padding-left:0; border-left:0;} .stat:nth-child(n+3){border-top:.5px solid var(--hair);} }

  /* ---------- closing band ---------- */
  .closer{position:relative; overflow:hidden; background:var(--pink); color:#fff; text-align:center; padding:clamp(4rem,2.5rem + 5vw,8rem) 24px;}
  .closer::before{content:""; position:absolute; inset:-40%; background:radial-gradient(closest-side, rgba(110,84,166,.85), transparent 70%) 80% 20%/60% 60% no-repeat, radial-gradient(closest-side, rgba(142,58,115,.9), transparent 70%) 10% 90%/70% 70% no-repeat;
    animation:t2g-drift 18s ease-in-out infinite alternate;}
  @keyframes t2g-drift{to{transform:translate(6%, -4%) rotate(8deg);}}
  .closer > *{position:relative;}
  .closer .chip{background:#fff; color:var(--pink);}
  .closer h2{font-size:var(--fs-h1); line-height:.98; margin:24px auto 22px; max-width:14ch;}
  .closer p{font-size:var(--fs-lead); color:rgba(255,255,255,.9); max-width:48ch; margin:0 auto 34px;}
  .closer .fine{display:block; margin-top:20px; font-family:var(--f-mono); font-size:.75rem; letter-spacing:.06em; text-transform:uppercase; color:rgba(255,255,255,.75);}
</style>
@endpush

@section('content')
@php
  $travelShots = \App\Support\Gallery::highlights(14);
  $places = ($samples ?? collect())->map(fn ($s) => [
      'name' => str_replace(' National Park', '', \Illuminate\Support\Str::before($s->destination, ',')),
      'url' => route('trips.show', $s),
  ])->unique('name')->values();
@endphp

{{-- ================= HERO ================= --}}
<header class="hero">
  <span class="chip reveal"><span class="dot pulse"></span> AI-drafted · group-editable · free in beta</span>
  <h1 data-split>Plan the trip <span class="gtext">together</span>, not in the group chat.</h1>
  <p class="lead reveal d2">Say where, when and who's coming. Get a full day-by-day plan back — meals, stops, costs, rainy-day backups — that the whole group shapes in one place.</p>
  <div class="cta-row reveal d3">
    <a class="btn btn-primary btn-lg" href="{{ route('trips.create') }}">Plan my first trip <span class="arr" aria-hidden="true"></span></a>
    @if($sampleTrip ?? null)
      <a class="btn btn-ghost btn-lg" href="#live">See a live sample</a>
    @endif
  </div>
  <p class="trust reveal d4">No card required · <b>Up to {{ \App\Models\User::FREE_TRIP_LIMIT }} trips free</b></p>

  <div class="stage reveal" id="stage">
    <div class="stage-photo">
      <picture>
        <source srcset="{{ asset('img/hero/grand-canyon.webp') }}" type="image/webp">
        <img src="{{ asset('img/hero/grand-canyon.jpg') }}" width="1050" height="1400" fetchpriority="high" decoding="async" data-parallax="0.12"
             alt="Two travellers wrapped up together on the rim of the Grand Canyon, looking out over the layered red cliffs under a wide blue sky">
      </picture>
      <svg class="route" viewBox="0 0 1200 600" preserveAspectRatio="none" aria-hidden="true">
        <path class="draw" d="M120 470 C 300 360, 380 520, 560 380 S 860 180, 1080 250"/>
        <circle class="pin" cx="120" cy="470" r="8"/><circle class="pin" cx="560" cy="380" r="8"/><circle class="pin" cx="1080" cy="250" r="8"/>
      </svg>
      <span class="stage-cap">Grand Canyon, Arizona · Day 3</span>
    </div>

    <div class="float f1" aria-hidden="true"><div class="bob">
      <div class="k"><i></i> Lunch · 12:30</div>
      <div class="row"><b>Locked: Phantom Ranch Canteen</b></div>
      <div style="margin-top:8px; display:flex; align-items:center; gap:10px; color:var(--text-dim); font-size:.8125rem;">
        <span><span class="av">M</span><span class="av">J</span><span class="av">L</span></span>&nbsp; 3 of 4 picked this
      </div>
    </div></div>
    <div class="float f2" aria-hidden="true"><div class="bob" style="animation-delay:-2s">
      <div class="k"><i style="background:#5B8C7B"></i> Budget · live</div>
      <div class="row"><b>Trip total</b><span class="delta">−$48 vs plan</span></div>
      <div class="bar"><span></span></div>
    </div></div>
    <div class="float f3" aria-hidden="true"><div class="bob" style="animation-delay:-4s">
      <div class="k"><i style="background:var(--lavender)"></i> Weather swap</div>
      <div class="row"><b>Storms 3pm → Visitor Center</b></div>
      <div style="margin-top:6px; color:var(--text-dim); font-size:.8125rem;">Indoor backup already in the plan</div>
    </div></div>
  </div>
</header>

{{-- ================= MARQUEE ================= --}}
<section class="ribbon" aria-label="Places">
  <div class="t2g-wrap ribbon-head">
    <p>@if(count($travelShots)) Built by a traveller — every photo is from my own trips. @else Ready-made sample plans you can copy and change. @endif</p>
    <button type="button" class="pause" data-marquee-pause="mq" aria-pressed="false">Pause</button>
  </div>
  <div class="marquee" id="mq" style="--dur:{{ count($travelShots) ? 70 : 40 }}s">
    <div class="marquee-track">
      @if(count($travelShots))
        @foreach($travelShots as $shot)
          <a href="{{ route('gallery.show', $shot['slug']) }}" class="shot">
            <img src="{{ \App\Support\Gallery::thumb($shot['slug'], $shot) }}" alt="{{ $shot['place'] }}" loading="lazy" decoding="async">
            <span>{{ $shot['place'] }}</span>
          </a>
        @endforeach
      @else
        @foreach($places as $p)
          <a class="place" href="{{ $p['url'] }}">{{ $p['name'] }}</a>
        @endforeach
      @endif
    </div>
  </div>
</section>

{{-- ================= BEFORE / AFTER ================= --}}
<section class="sec">
  <div class="t2g-wrap">
    <div class="eyebrow sec-head">
      <span class="chip chip-line reveal">The problem</span>
      <h2 data-split>200 unread messages is not a plan.</h2>
    </div>
    <div class="versus">
      <div class="before reveal">
        <span class="chip chip-line">In the group chat</span>
        <h3>Everyone's ideas, nobody's decision</h3>
        <ul>
          @foreach(['200 unread messages, three abandoned Google Docs', '"Did anyone actually book the restaurant?"', 'No one remembers why you picked that hotel', 'The rainy-day plan lives in someone\'s head'] as $i => $t)
            <li style="--i:{{ $i }}"><span class="n">0{{ $i + 1 }}</span><span class="t">{{ $t }}</span></li>
          @endforeach
        </ul>
      </div>
      <div class="after reveal d2">
        <span class="chip chip-pink">On Travel2gether</span>
        <h3>One plan, every pick visible</h3>
        <ul>
          @foreach(['One itinerary, every day laid out with 3+ options per slot', 'Tap a card to lock a pick — everyone sees who chose it', 'A live budget that moves as picks change', 'An indoor backup already attached to every outdoor stop'] as $i => $t)
            <li><span class="n">0{{ $i + 1 }}</span><span>{{ $t }}</span></li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</section>

{{-- ================= PRODUCT TABS ================= --}}
<section class="sec prod" id="product">
  <div class="t2g-wrap" data-tabs data-autoplay="7000">
    <div class="eyebrow sec-head">
      <span class="chip chip-pink reveal">Inside a plan</span>
      <h2 data-split>Everything the group argues about, made concrete.</h2>
      <p class="reveal d2">Options, roles, money and weather — in one plan that updates for everyone.</p>
    </div>
    <div class="tablist" role="tablist" aria-label="Travel2gether features">
      <button class="tab" role="tab" id="t-opt" aria-controls="p-opt">Options</button>
      <button class="tab" role="tab" id="t-share" aria-controls="p-share">Share</button>
      <button class="tab" role="tab" id="t-budget" aria-controls="p-budget">Budget</button>
      <button class="tab" role="tab" id="t-wx" aria-controls="p-wx">Weather</button>
    </div>

    <div class="panel" role="tabpanel" id="p-opt" aria-labelledby="t-opt">
      <div class="p-copy">
        <span class="chip chip-line" style="color:#fff; box-shadow:inset 0 0 0 .5px rgba(255,255,255,.4)">01 · Options</span>
        <h3>Three picks per slot, not one take-it-or-leave-it.</h3>
        <p>Every meal, activity and shopping stop comes with budget, mid and splurge choices. Tap one to lock it.</p>
        <ul><li>Budget / mid / splurge on every slot</li><li>Cost tag and walking notes on each card</li><li>Your pick is remembered for the whole group</li></ul>
      </div>
      <div class="mock" aria-hidden="true">
        <div class="m-k"><span>Day 2 · Dinner · 19:00</span><span>Tokyo</span></div>
        <div class="opt"><span>Ichiran Shinjuku<small>Ramen booth · 6 min walk</small></span><span class="tier">¥ Budget</span></div>
        <div class="opt on"><span>Omoide Yokocho<small>Yakitori alley · 4 min walk</small></span><span class="tier">¥¥ Mid · picked</span></div>
        <div class="opt"><span>New York Grill<small>Park Hyatt 52F · reserve</small></span><span class="tier">¥¥¥ Splurge</span></div>
      </div>
    </div>

    <div class="panel" role="tabpanel" id="p-share" aria-labelledby="t-share" hidden>
      <div class="p-copy">
        <span class="chip chip-line" style="color:#fff; box-shadow:inset 0 0 0 .5px rgba(255,255,255,.4)">02 · Share</span>
        <h3>One link. Clear roles. No more "who decided this?"</h3>
        <p>Owners lock decisions, editors propose and vote, viewers just look. Every pick shows who made it.</p>
        <ul><li>Invite links with an expiry you control</li><li>Owner, editor and viewer roles</li><li>See who's planning right now</li></ul>
      </div>
      <div class="mock" aria-hidden="true">
        <div class="m-k"><span>Who's on this trip</span><span><span class="chip" style="height:22px; font-size:.625rem; background:#2f7d58"><span class="dot"></span> 2 here now</span></span></div>
        <div class="who"><span class="av">L</span> Lea<span class="role">Owner</span></div>
        <div class="who"><span class="av" style="background:var(--lavender)">J</span> Jun<span class="role">Editor</span></div>
        <div class="who"><span class="av" style="background:var(--pink)">M</span> Mia<span class="role">Editor</span></div>
        <div class="who"><span class="av" style="background:#8E7986">T</span> Tita Rose<span class="role">Viewer</span></div>
      </div>
    </div>

    <div class="panel" role="tabpanel" id="p-budget" aria-labelledby="t-budget" hidden>
      <div class="p-copy">
        <span class="chip chip-line" style="color:#fff; box-shadow:inset 0 0 0 .5px rgba(255,255,255,.4)">03 · Budget</span>
        <h3>Change a pick, watch the number move.</h3>
        <p>Cost ranges roll into a running total and a per-person daily average, so the money talk happens early.</p>
        <ul><li>Live worksheet by category</li><li>Per-day and per-person averages</li><li>Your own currency</li></ul>
      </div>
      <div class="mock" aria-hidden="true">
        <div class="m-k"><span>Budget · 4 travellers</span><span>USD</span></div>
        <div class="bars">
          @foreach([['Accommodation', '#E88FAE', .92, '$1,240'], ['Meals', '#C77DA2', .58, '$780'], ['Transport', '#5B8C7B', .41, '$552'], ['Activities', '#B48FD9', .33, '$446'], ['Shopping', '#D98FC2', .2, '$270']] as $n => [$label, $c, $w, $amt])
            <div class="b" style="--c:{{ $c }}; --w:{{ $w }}; --n:{{ $n }}"><span>{{ $label }}</span><i></i><span>{{ $amt }}</span></div>
          @endforeach
        </div>
        <div class="total"><span class="m-k" style="margin:0">Trip total</span><b>$3,288</b></div>
      </div>
    </div>

    <div class="panel" role="tabpanel" id="p-wx" aria-labelledby="t-wx" hidden>
      <div class="p-copy">
        <span class="chip chip-line" style="color:#fff; box-shadow:inset 0 0 0 .5px rgba(255,255,255,.4)">04 · Weather</span>
        <h3>Rain at three? The backup is already there.</h3>
        <p>When the forecast turns, outdoor stops get flagged and the pre-picked indoor option is one tap away — with the cost difference shown.</p>
        <ul><li>Hourly forecast for each day's area</li><li>Indoor backup on every outdoor stop</li><li>The cost difference before you swap</li></ul>
      </div>
      <div class="mock" aria-hidden="true">
        <div class="m-k"><span>Day 4 · Kamakura</span><span>Hourly</span></div>
        <div class="wx">
          @foreach([['09', '☀', '21°'], ['11', '⛅', '23°'], ['13', '⛅', '22°'], ['15', '☂', '19°', 1], ['17', '☂', '18°', 1], ['19', '☁', '17°']] as $h)
            <div class="{{ isset($h[3]) ? 'rain' : '' }}">{{ $h[0] }}:00<b>{{ $h[1] }}</b>{{ $h[2] }}</div>
          @endforeach
        </div>
        <div class="swap">
          <div class="opt"><span>Hase hike<small>Outdoor · 15:00</small></span></div>
          <span class="arrow">→</span>
          <div class="opt on"><span>Kamakura Museum<small>Indoor · +¥400</small></span></div>
        </div>
      </div>
    </div>
    <div class="tabs-foot"><button type="button" class="pause" data-tabs-pause>Stop auto-advance</button></div>
  </div>
</section>

{{-- ================= BENEFITS ================= --}}
<section class="sec">
  <div class="t2g-wrap">
    <div class="eyebrow sec-head">
      <span class="chip reveal">Why it works</span>
      <h2 data-split>Built for the messy middle of planning.</h2>
    </div>
    <div class="grid6" data-stagger>
      @php
        $benefits = [
          ['Options', '3+ picks per slot', 'Budget, mid and splurge for every meal and stop — plus an indoor one for bad weather.', '<path d="M4 6h16M4 12h16M4 18h10"/>'],
          ['Budget', 'A live worksheet', 'Cost ranges roll into a running total. Change a pick and the number moves.', '<path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>'],
          ['Weather', 'Rainy-day backups', 'Bad forecast? Outdoor stops get flagged with the indoor alternative and the cost difference.', '<path d="M7 17a5 5 0 1 1 1-9.9A6 6 0 0 1 19 9a4 4 0 0 1-1 8H7zM9 20l-1 2M13 20l-1 2M17 20l-1 2"/>'],
          ['Together', 'Share with roles', 'Send a link. Owners lock decisions, editors vote, viewers look — like a shared doc.', '<circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2 20a6 6 0 0 1 12 0M14 20a4.5 4.5 0 0 1 8 0"/>'],
          ['Honesty', 'Hiccups, written down', 'Every day spells out what could go wrong — the stall that closes early, the 45-minute queue.', '<path d="M12 3 2 21h20L12 3zM12 10v5M12 18v.5"/>'],
          ['Reuse', 'Remix any trip', 'Found a sample you like? Copy it as your own starting point and change it from there.', '<path d="M4 12a8 8 0 0 1 14-5.3L20 9M20 4v5h-5M20 12a8 8 0 0 1-14 5.3L4 15M4 20v-5h5"/>'],
        ];
      @endphp
      @foreach($benefits as $i => [$tag, $title, $body, $icon])
        <div class="cell spot">
          <div class="ix"><span>0{{ $i + 1 }}</span><em>{{ $tag }}</em></div>
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
          <h3>{{ $title }}</h3>
          <p>{{ $body }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ================= HICCUPS + LIVE SAMPLE ================= --}}
<section class="t2g-wrap" id="live">
  <div class="quote-sec">
    <article>
      <span class="chip chip-plum reveal" style="width:fit-content">The part other planners leave out</span>
      <div class="q-wrap">
        <div class="q-rail" aria-hidden="true"><span>“</span><i></i></div>
        <div class="q-stack" id="qStack" aria-live="polite">
          <blockquote><p>teamLab Planets sells out days ahead — book before you fly.</p><footer>Hiccup · Day 1 · Shinjuku</footer></blockquote>
          <blockquote aria-hidden="true"><p>Okutama is cash country — draw money at Shinjuku; the ATMs up here are few and close early.</p><footer>Hiccup · Day 2 · Okutama</footer></blockquote>
          <blockquote aria-hidden="true"><p>Sunday makes Kamakura busy and the Enoden slow — start at Tsurugaoka Hachimangū by 09:00.</p><footer>Hiccup · Day 4 · Kamakura</footer></blockquote>
        </div>
      </div>
      <div class="q-dots" id="qDots" role="group" aria-label="Choose a hiccup">
        <button type="button" aria-pressed="true" aria-label="Hiccup 1"></button>
        <button type="button" aria-pressed="false" aria-label="Hiccup 2"></button>
        <button type="button" aria-pressed="false" aria-label="Hiccup 3"></button>
      </div>
    </article>

    @if($sampleTrip ?? null)
    <div class="live reveal d1">
      <div class="live-head">
        <span class="chip"><span class="dot pulse"></span> Live sample — tap around</span>
        @if(($samples ?? collect())->count() > 1)
          <div class="sb-tabs">
            @foreach($samples->take(6) as $s)
              <button type="button" class="sb-tab {{ $loop->first ? 'on' : '' }}" data-src="{{ route('trips.show', $s) }}" title="{{ $s->title }}">{{ str_replace(' National Park', '', \Illuminate\Support\Str::before($s->destination, ',')) }}</button>
            @endforeach
          </div>
        @endif
      </div>
      <iframe id="sbFrame" src="{{ route('trips.show', $sampleTrip) }}" title="Sample itinerary preview" loading="lazy"></iframe>
      <div class="live-note"><span>A real Travel2gether plan — not a screenshot.</span><a id="sbOpen" href="{{ route('trips.show', $sampleTrip) }}">Open full sample →</a></div>
    </div>
    @endif
  </div>
</section>

{{-- ================= HOW IT WORKS ================= --}}
<section class="sec" id="how">
  <div class="t2g-wrap">
    <div class="eyebrow sec-head">
      <span class="chip chip-line reveal">How it works</span>
      <h2 data-split>A first draft in about a minute.</h2>
      <p class="reveal d2">Shaping it with your group takes as long as you want.</p>
    </div>
    <div class="steps reveal">
      <div class="stp"><span class="big">01</span><h3>Describe it</h3><p>Destination, dates, who's coming, rough budget, what you're into. A sentence or two is enough.</p></div>
      <div class="stp"><span class="big">02</span><h3>Get a full draft</h3><p>Day-by-day stops with options per slot, cost tags, weather notes and the things that could go wrong.</p></div>
      <div class="stp"><span class="big">03</span><h3>Shape it together</h3><p>Invite the group with a link. Vote on options, swap stops, edit the budget. Every pick shows who made it.</p></div>
    </div>
    <div class="stats">
      <div class="stat"><b data-count="{{ max(1, ($samples ?? collect())->count()) }}">{{ max(1, ($samples ?? collect())->count()) }}</b><span>Sample trips to copy</span></div>
      <div class="stat"><b data-count="3" data-suffix="+">3+</b><span>Options per time slot</span></div>
      <div class="stat"><b data-count="{{ \App\Models\User::FREE_TRIP_LIMIT }}">{{ \App\Models\User::FREE_TRIP_LIMIT }}</b><span>Trips free in beta</span></div>
      <div class="stat"><b data-count="0" data-prefix="$">$0</b><span>Card needed to start</span></div>
    </div>
  </div>
</section>

{{-- ================= CLOSER ================= --}}
<section class="closer">
  <span class="chip reveal">Free while we're in beta</span>
  <h2 data-split>Your next trip starts with one sentence.</h2>
  <p class="reveal d2">Plan up to {{ \App\Models\User::FREE_TRIP_LIMIT }} trips at a time — full day-by-day plans, group sharing, the budget and the weather backups.</p>
  <a class="btn btn-white btn-lg reveal d3" href="{{ route('trips.create') }}">Start planning — free <span class="arr" aria-hidden="true"></span></a>
  <span class="fine reveal d4">No account needed to start · save it when it's ready</span>
</section>

<script>
(function () {
  // Live sample switcher
  var frame = document.getElementById('sbFrame'), open = document.getElementById('sbOpen');
  document.querySelectorAll('.sb-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.sb-tab').forEach(function (b) { b.classList.toggle('on', b === btn); });
      if (frame) frame.src = btn.dataset.src;
      if (open) open.href = btn.dataset.src;
    });
  });

  // Hiccup quotes: rotate every 6s until the visitor picks one
  var qs = document.querySelectorAll('#qStack blockquote'), dots = document.querySelectorAll('#qDots button'), cur = 0, timer;
  function show(i) {
    cur = i;
    qs.forEach(function (q, j) { q.setAttribute('aria-hidden', j === i ? 'false' : 'true'); });
    dots.forEach(function (d, j) { d.setAttribute('aria-pressed', j === i ? 'true' : 'false'); });
  }
  dots.forEach(function (d, i) { d.addEventListener('click', function () { clearInterval(timer); show(i); }); });
  var calm = document.documentElement.getAttribute('data-motion') === 'reduce' || matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!calm && qs.length) timer = setInterval(function () { show((cur + 1) % qs.length); }, 6000);
})();
</script>
@endsection
