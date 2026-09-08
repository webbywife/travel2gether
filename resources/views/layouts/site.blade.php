<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Travel2gether — plan the trip together')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="@yield('meta_description', 'Describe your trip and get a full day-by-day itinerary your whole group can shape together — options, live budget, weather backups.')">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#FFFFFF; --panel:#FFF8FA; --panel-2:#FDEAF1;
    --pink:#C22A66; --pink-light:#F6A9C6; --accent:#9E4A6E; --lavender:#7156A8;
    --text:#3A2E38; --text-dim:#6B5860; --line:rgba(58,46,56,0.12);
  }
  *{box-sizing:border-box;}
  body{
    margin:0; color:var(--text); font-family:'Inter',sans-serif; font-size:17px; line-height:1.6;
    background:
      linear-gradient(rgba(255,255,255,0.62), rgba(255,255,255,0.62)),
      radial-gradient(circle at 12% 8%, rgba(225,74,128,0.05), transparent 40%),
      radial-gradient(circle at 88% 18%, rgba(180,143,217,0.05), transparent 40%),
      url('/img/bg-pattern.jpg'),
      var(--bg);
    background-repeat:no-repeat, no-repeat, no-repeat, repeat, no-repeat;
    background-size:auto, auto, auto, 1100px auto, auto;
    background-attachment:fixed, fixed, fixed, fixed, fixed;
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
    body{background-attachment:scroll, scroll, scroll, scroll;}
    .parallax-root .p .pi{animation:none !important;}
  }
  .site-nav, .page, .site-foot{position:relative; z-index:2;}

  .site-nav{
    max-width:1080px; margin:0 auto; padding:20px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
  }
  .brand{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:20px; color:var(--text); text-decoration:none; letter-spacing:-0.01em;}
  .brand b{color:var(--pink);}
  .site-nav .links{display:flex; align-items:center; gap:22px; flex-wrap:wrap;}
  .site-nav .links a{color:var(--text-dim); text-decoration:none; font-size:14.5px;}
  .site-nav .links a:hover{color:var(--text);}
  .site-nav .links a.btn-primary{color:#fff;}
  .site-nav .links a.btn-primary:hover{color:#fff;}
  .site-nav .links a.btn-ghost{color:var(--pink);}
  .btn{
    display:inline-block; font-family:'Inter',sans-serif; font-size:14.5px; font-weight:600;
    padding:10px 18px; border-radius:999px; text-decoration:none; cursor:pointer; border:1px solid var(--pink);
  }
  .btn-primary{background:var(--pink); color:#fff;}
  .btn-primary:hover{background:#a82357;}
  .btn-ghost{background:transparent; color:var(--pink);}
  .btn-ghost:hover{background:rgba(225,74,128,0.08);}

  .shell{max-width:1080px; margin:0 auto; padding:0 24px 80px;}
  .site-foot{
    max-width:1080px; margin:0 auto; padding:28px 24px 48px; border-top:1px solid var(--line);
    display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;
    color:var(--text-dim); font-size:13.5px;
  }
  .stamp{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--pink); border:1px solid var(--pink); border-radius:999px; padding:7px 14px; letter-spacing:0.06em;}

  /* Auth + narrow content pages */
  .card-wrap{max-width:420px; margin:40px auto 0;}
  .card{background:rgb(255 255 255 / 92%); border:2px solid var(--pink); border-radius:14px; padding:28px;}
  .card h1{font-family:'Space Grotesk',sans-serif; font-size:26px; margin:0 0 6px;}
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
  @media (max-width:560px){ .site-nav{padding:16px;} .site-nav .links{gap:14px;} }
</style>
@stack('styles')
</head>
<body>
@stack('parallax')
<nav class="site-nav">
  <a class="brand" href="{{ route('home') }}">Travel<b>2</b>gether</a>
  <div class="links">
    <a href="{{ route('home') }}#how">How it works</a>
    @if($sampleTrip ?? null)<a href="{{ route('trips.show', $sampleTrip) }}">Sample trip</a>@endif
    @auth
      <a href="{{ route('dashboard') }}">My trips</a>
      <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
        <button type="submit" class="links-logout" style="background:none;border:none;color:var(--text-dim);font:inherit;font-size:14.5px;cursor:pointer;padding:0;">Log out</button>
      </form>
    @else
      <a href="{{ route('login') }}">Log in</a>
      <a class="btn btn-primary" href="{{ route('register') }}">Start planning</a>
    @endauth
  </div>
</nav>

<div class="page">
@yield('content')
</div>

<footer class="site-foot">
  <span>Travel2gether — AI-drafted itineraries your group shapes together.</span>
  <span class="stamp">PLAN · TOGETHER · {{ date('Y') }}</span>
</footer>
</body>
</html>
