{{-- The one site header: used by layouts.site and by the trip pages, so every page shares it. Self-contained styles. --}}
<style>
  .navbar{--nav-grad:linear-gradient(135deg, #C22A66, #8E3A73); --nav-line:rgba(36,30,35,0.10); font-family:'Inter',sans-serif; line-height:1.6;}
  .navbar{
    position:sticky; top:0; z-index:10000;
    background:rgba(255,255,255,0.78);
    -webkit-backdrop-filter:saturate(160%) blur(12px);
    backdrop-filter:saturate(160%) blur(12px);
    border-bottom:1px solid var(--nav-line);
  }
  .site-nav{
    max-width:1120px; margin:0 auto; padding:15px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
  }
  .brand{display:flex; align-items:center; gap:9px; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:20px; text-decoration:none; letter-spacing:-0.015em;}
  .brand-mark{height:38px; width:auto; display:block;}
  .brand .gtext{padding-right:1px;}
  .brand-word{height:28px; width:auto; display:block;}
  @media (max-width:480px){ .brand-word{height:20px;} }
  .site-nav .links{display:flex; align-items:center; gap:24px; flex-wrap:wrap;}
  .site-nav .links a{color:var(--text-dim); text-decoration:none; font-size:14.5px; font-weight:500;}
  .site-nav .links a:hover{color:var(--text);}
  .site-nav .links a.btn-primary{color:#fff;}
  .site-nav .links a.btn-primary:hover{color:#fff;}
  .site-nav .links a.btn-ghost{color:var(--pink);}
  .navbar .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    font-family:'Inter',sans-serif; font-size:14.5px; font-weight:600; line-height:1.2;
    padding:11px 20px; border-radius:999px; text-decoration:none; cursor:pointer;
    border:1px solid transparent; transition:transform .15s ease, box-shadow .15s ease;
  }
  .navbar .btn-primary{background:var(--nav-grad); color:#fff; box-shadow:0 8px 20px -8px rgba(194,42,102,0.55);}
  .navbar .btn-primary:hover{transform:translateY(-1px); box-shadow:0 12px 26px -8px rgba(194,42,102,0.6);}
  @media (max-width:560px){ .site-nav{padding:12px 16px;} }

  /* Mobile menu: logo · main button · ☰ on one line, the rest in a drop-down */
  .navbar .nav-cta, .navbar .nav-toggle{display:none;}
  @media (max-width:860px){
    .site-nav{flex-wrap:nowrap; position:relative;}
    .brand{margin-right:auto;}
    .navbar .nav-cta{display:inline-flex; padding:9px 16px; font-size:14px; white-space:nowrap;}
    .navbar .nav-toggle{display:inline-flex; flex-direction:column; justify-content:center; gap:5px; width:42px; height:42px; flex-shrink:0;
      padding:0 10px; border:1px solid var(--nav-line); border-radius:12px; background:rgba(255,255,255,0.7); cursor:pointer;}
    .nav-toggle span{display:block; height:2px; border-radius:2px; background:var(--text); transition:transform .2s ease, opacity .2s ease;}
    .site-nav.open .nav-toggle span:nth-child(1){transform:translateY(7px) rotate(45deg);}
    .site-nav.open .nav-toggle span:nth-child(2){opacity:0;}
    .site-nav.open .nav-toggle span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}
    .site-nav .links{display:none; position:absolute; top:100%; left:0; right:0; flex-direction:column; align-items:stretch; gap:0;
      background:rgba(255,255,255,0.98); border-bottom:1px solid var(--nav-line); box-shadow:0 18px 30px -18px rgba(36,30,35,0.25); padding:6px 16px 14px;}
    .site-nav.open .links{display:flex;}
    .site-nav .links a, .site-nav .links .links-logout{padding:13px 4px !important; font-size:16px !important; border-bottom:1px solid var(--nav-line); text-align:left;}
    .site-nav .links form{display:block !important;}
    .site-nav .links .btn-primary{display:none;}   /* already in the bar */
    .site-nav .links a:last-of-type{border-bottom:0;}
  }
  @media (max-width:380px){ .navbar .nav-cta{padding:8px 12px; font-size:13px;} }
</style>
<header class="navbar">
  <nav class="site-nav">
    <a class="brand" href="{{ route('home') }}" aria-label="Travel2gether — home">
      <img class="brand-word" src="{{ asset('img/wordmark.png') }}" width="152" height="28" alt="Travel2gether">
    </a>
    @auth
      <a class="btn btn-primary nav-cta" href="{{ route('trips.create') }}">New trip</a>
    @else
      <a class="btn btn-primary nav-cta" href="{{ route('trips.create') }}">Start planning</a>
    @endauth
    <button type="button" class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="siteLinks" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <div class="links" id="siteLinks">
      <a href="{{ route('home') }}#how">How it works</a>
      <a href="{{ route('destinations') }}">Destinations</a>
      @if(\App\Support\Gallery::places())<a href="{{ route('gallery') }}">Gallery</a>@endif
      <a href="{{ route('samples') }}">Sample trips</a>
      @auth
        <a href="{{ route('dashboard') }}">My trips</a>
        <a href="{{ route('profile.edit') }}">Profile</a>
        @can('admin')<a href="{{ route('analytics') }}">Analytics</a>@endcan
        <a class="btn btn-primary" href="{{ route('trips.create') }}">New trip</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
          <button type="submit" class="links-logout" style="background:none;border:none;color:var(--text-dim);font:inherit;font-size:14.5px;cursor:pointer;padding:0;">Log out</button>
        </form>
      @else
        <a href="{{ route('login') }}">Log in</a>
        <a class="btn btn-primary" href="{{ route('trips.create') }}">Start planning</a>
      @endauth
    </div>
  </nav>
</header>
<script>
(function () {
  var nav = document.querySelector('.site-nav'), btn = document.getElementById('navToggle');
  if (!nav || !btn) return;
  function set(open) { nav.classList.toggle('open', open); btn.setAttribute('aria-expanded', open); btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu'); }
  btn.addEventListener('click', function () { set(!nav.classList.contains('open')); });
  document.getElementById('siteLinks').addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  document.addEventListener('click', function (e) { if (!nav.contains(e.target)) set(false); });
})();
</script>
