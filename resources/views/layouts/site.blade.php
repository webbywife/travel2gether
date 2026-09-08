<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Travel2gether — plan the trip together')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="@yield('meta_description', 'Describe your trip and get a full day-by-day itinerary your whole group can shape together — options, live budget, weather backups.')">
<link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#FFFFFF; --panel:#FFF8FA; --panel-2:#FDEAF1;
    --pink:#C22A66; --pink-light:#F6A9C6; --accent:#9E4A6E; --lavender:#7156A8;
    --text:#241E23; --text-dim:#6B5860; --line:rgba(36,30,35,0.10);
    --grad:linear-gradient(102deg, #C22A66 0%, #A5357A 46%, #6E54A6 100%);
    --grad-soft:linear-gradient(135deg, #C22A66, #8E3A73);
    --shadow-sm:0 1px 2px rgba(36,30,35,0.04), 0 6px 16px -8px rgba(36,30,35,0.10);
    --shadow-md:0 2px 6px rgba(36,30,35,0.05), 0 18px 40px -18px rgba(120,40,80,0.22);
  }
  *{box-sizing:border-box;}

  /* Gradient text utility, used on headings throughout */
  .gtext{
    background:var(--grad); -webkit-background-clip:text; background-clip:text;
    -webkit-text-fill-color:transparent; color:transparent;
  }
  body{
    margin:0;
    color:var(--text);
    font-family:'Inter', sans-serif;
    font-size:17px;
    line-height:1.6;
    background: linear-gradient(rgba(255, 255, 255, 0.62), rgba(255, 255, 255, 0.62)), radial-gradient(circle at 12% 8%, rgba(225, 74, 128, 0.05), transparent 40%), radial-gradient(circle at 88% 18%, rgba(180, 143, 217, 0.05), transparent 40%), url(/img/bg-pattern.jpg), var(--bg);
    background-repeat: repeat-x;
    background-size: 1200px;
    background-position: center, center, center, center top, center;
    background-attachment: fixed, fixed, fixed, fixed, fixed;
    border-top: 5px solid #bf2a64;
  }
  a{color:var(--pink);}
  .mono{font-family:'JetBrains Mono',monospace;}

  /* Parallax travel doodles behind everything */
  .parallax-root{position:fixed; inset:0; z-index:0; overflow:hidden; pointer-events:none;}
  .parallax-root .p{position:absolute; will-change:transform; color:var(--pink); opacity:0.22;}
  .parallax-root .p .pi{display:block; width:100%; height:100%;}
  .parallax-root .p svg{display:block; width:100%; height:100%;}
  .parallax-root .p.balloon .pi{animation:t2g-float 9s ease-in-out infinite;}
  .parallax-root .p.ship .pi{animation:t2g-bob 7s ease-in-out infinite;}
  @keyframes t2g-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
  @keyframes t2g-bob{0%,100%{transform:translateY(0) rotate(-1.5deg)}50%{transform:translateY(6px) rotate(1.5deg)}}
  @media (prefers-reduced-motion: reduce){
    body{background-attachment:scroll;}
    .parallax-root .p .pi{animation:none !important;}
  }
  .page, .site-foot{position:relative; z-index:1;}

  .navbar{
    position:sticky; top:0; z-index:10000;
    background:rgba(255,255,255,0.78);
    -webkit-backdrop-filter:saturate(160%) blur(12px);
    backdrop-filter:saturate(160%) blur(12px);
    border-bottom:1px solid var(--line);
  }
  .site-nav{
    max-width:1120px; margin:0 auto; padding:15px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
  }
  .brand{display:flex; align-items:center; gap:9px; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:20px; text-decoration:none; letter-spacing:-0.015em;}
  .brand-mark{height:38px; width:auto; display:block;}
  .brand .gtext{padding-right:1px;}
  @media (max-width:480px){ .brand span{display:none;} .brand-mark{height:30px;} }
  .site-nav .links{display:flex; align-items:center; gap:24px; flex-wrap:wrap;}
  .site-nav .links a{color:var(--text-dim); text-decoration:none; font-size:14.5px; font-weight:500;}
  .site-nav .links a:hover{color:var(--text);}
  .site-nav .links a.btn-primary{color:#fff;}
  .site-nav .links a.btn-primary:hover{color:#fff;}
  .site-nav .links a.btn-ghost{color:var(--pink);}
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    font-family:'Inter',sans-serif; font-size:14.5px; font-weight:600;
    padding:11px 20px; border-radius:999px; text-decoration:none; cursor:pointer;
    border:1px solid transparent; transition:transform .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .btn-primary{background:var(--grad-soft); color:#fff; box-shadow:0 8px 20px -8px rgba(194,42,102,0.55);}
  .btn-primary:hover{transform:translateY(-1px); box-shadow:0 12px 26px -8px rgba(194,42,102,0.6);}
  .btn-ghost{background:rgba(255,255,255,0.6); color:var(--pink); border-color:var(--pink-light);}
  .btn-ghost:hover{background:#fff; border-color:var(--pink); transform:translateY(-1px);}

  .shell{max-width:1080px; margin:0 auto; padding:0 24px 80px;}
  .site-foot{
    max-width:1080px; margin:0 auto; padding:28px 24px 48px; border-top:1px solid var(--line);
    display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;
    color:var(--text-dim); font-size:13.5px;
  }
  .stamp{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--pink); border:1px solid var(--pink); border-radius:999px; padding:7px 14px; letter-spacing:0.06em;}

  /* Auth + narrow content pages */
  .card-wrap{max-width:428px; margin:56px auto 0;}
  .card{background:rgba(255,255,255,0.94); border:1px solid var(--line); border-radius:20px; padding:32px; box-shadow:var(--shadow-md);}
  .card h1{font-family:'Space Grotesk',sans-serif; font-size:27px; margin:0 0 6px; letter-spacing:-0.015em;}
  .card .lede{color:var(--text-dim); font-size:14.5px; margin:0 0 22px;}
  .field{margin-bottom:16px;}
  .field label{display:block; font-size:13px; color:var(--text-dim); margin-bottom:5px; letter-spacing:0.02em;}
  .field input{
    width:100%; padding:11px 13px; border:1px solid var(--line); border-radius:10px;
    background:var(--panel); font-family:'Inter',sans-serif; font-size:15px; color:var(--text);
  }
  .field input:focus{outline:2px solid var(--pink-light); border-color:var(--pink);}
  .form-row{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; font-size:13.5px; color:var(--text-dim);}
  .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:10px 14px; font-size:13.5px; margin-bottom:18px;}
  .form-error ul{margin:0; padding-left:18px;}
  .btn-block{display:block; width:100%; text-align:center; border:none; font-size:15px; padding:12px;}
  .card-alt{text-align:center; margin-top:18px; font-size:13.5px; color:var(--text-dim);}

  .btn-google{
    display:flex; align-items:center; justify-content:center; gap:10px; width:100%;
    padding:11px 12px; border-radius:10px; border:1px solid var(--line); background:#fff;
    color:var(--text); font-family:'Inter',sans-serif; font-size:14.5px; font-weight:600;
    text-decoration:none; cursor:pointer; margin-bottom:16px;
  }
  .btn-google:hover{background:var(--panel); border-color:var(--pink-light);}
  .btn-google svg{width:18px; height:18px; flex-shrink:0;}
  .or-divider{display:flex; align-items:center; gap:12px; color:var(--text-dim); font-size:12px; letter-spacing:0.08em; text-transform:uppercase; margin:0 0 16px;}
  .or-divider::before, .or-divider::after{content:""; flex:1; height:1px; background:var(--line);}
  @media (max-width:560px){ .site-nav{padding:14px 16px;} .site-nav .links{gap:14px;} }

  /* Smooth section-to-section navigation + scroll reveal */
  html{scroll-behavior:smooth; scroll-padding-top:84px;}
  .reveal{opacity:0; transform:translateY(22px); transition:opacity .7s cubic-bezier(.22,.61,.36,1), transform .7s cubic-bezier(.22,.61,.36,1);}
  .reveal.is-visible{opacity:1; transform:none;}
  .reveal.d1{transition-delay:.06s;} .reveal.d2{transition-delay:.12s;} .reveal.d3{transition-delay:.18s;}
  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
    .reveal{opacity:1 !important; transform:none !important; transition:none !important;}
  }
</style>
<noscript><style>.reveal{opacity:1 !important; transform:none !important;}</style></noscript>
@stack('styles')
</head>
<body>
@stack('parallax')
<header class="navbar">
  <nav class="site-nav">
    <a class="brand" href="{{ route('home') }}">
      <img class="brand-mark" src="{{ asset('img/logo-mark.png') }}" alt="Travel2gether">
      <span class="gtext">Travel2gether</span>
    </a>
    <div class="links">
      <a href="{{ route('home') }}#how">How it works</a>
      @if($sampleTrip ?? null)<a href="{{ route('trips.show', $sampleTrip) }}">Sample trip</a>@endif
      @auth
        <a href="{{ route('dashboard') }}">My trips</a>
        <a class="btn btn-primary" href="{{ route('trips.create') }}">New trip</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
          <button type="submit" class="links-logout" style="background:none;border:none;color:var(--text-dim);font:inherit;font-size:14.5px;cursor:pointer;padding:0;">Log out</button>
        </form>
      @else
        <a href="{{ route('login') }}">Log in</a>
        <a class="btn btn-primary" href="{{ route('register') }}">Start planning</a>
      @endauth
    </div>
  </nav>
</header>

<div class="page">
@yield('content')
</div>

<footer class="site-foot">
  <span>Travel2gether — AI-drafted itineraries your group shapes together.</span>
  <span class="stamp">PLAN · TOGETHER · {{ date('Y') }}</span>
</footer>

<script>
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var els = document.querySelectorAll('.reveal');
  if (!els.length || !('IntersectionObserver' in window)) {
    els.forEach(function (e) { e.classList.add('is-visible'); });
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
    });
  }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
  els.forEach(function (e) { io.observe(e); });
  // Safety net: never leave content hidden.
  window.addEventListener('load', function () {
    setTimeout(function () { els.forEach(function (e) { e.classList.add('is-visible'); }); }, 2500);
  });
})();
</script>
@include('partials.trippie')
</body>
</html>
