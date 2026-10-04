@extends('layouts.site')

@php
  use App\Support\Gallery;
  $years = $place['years'];
  $span = count($years) ? (count($years) > 1 ? reset($years) . '–' . end($years) : reset($years)) : '';
  $slides = collect($place['photos'])->map(fn ($p) => [
      'type' => $p['type'],
      'link' => $p['link'],
      'src' => Gallery::url($place['slug'], $p['file']),
      'poster' => $p['type'] === 'video' ? Gallery::url($place['slug'], $p['poster']) : null,
      'caption' => $p['caption'],
      'taken' => $p['taken'] ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $p['taken'])->format('M Y') : '',
  ])->values();
@endphp

@section('title', $place['name'] . ' — photos · Travel2gether')
@section('og_image', Gallery::cover($place['slug'], false))
@section('meta_description', count($place['photos']) . ' photos and videos from ' . $place['name'] . ($place['country'] ? ', ' . $place['country'] : '') . ', taken on real trips.')

@push('styles')
<style>
  .ph-head{max-width:1120px; margin:0 auto; padding:36px 24px 18px;}
  .ph-back{font-size:13.5px; text-decoration:none; color:var(--text-dim);}
  .ph-back:hover{color:var(--pink);}
  .ph-head h1{font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,40px); margin:10px 0 4px; letter-spacing:-0.02em;}
  .ph-sub{color:var(--text-dim); font-size:15px; display:flex; gap:14px; flex-wrap:wrap; align-items:center;}
  .ph-sub .btn{margin-left:auto;}
  .ph-guide{margin-top:18px; background:rgba(255,255,255,0.92); border:1px solid var(--line); border-radius:14px; padding:16px 18px;}
  .ph-guide .g-facts{display:flex; flex-wrap:wrap; gap:10px 26px; margin-bottom:10px;}
  .ph-guide .g-facts span{font-size:14px;}
  .ph-guide .g-facts b{display:block; font-family:'JetBrains Mono',monospace; font-size:10.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-dim); font-weight:500; margin-bottom:2px;}
  .ph-guide p{margin:0 0 14px; color:var(--text-dim); line-height:1.6; font-size:14.5px; max-width:75ch;}
  .ph-guide .g-cta{display:flex; flex-wrap:wrap; gap:8px 14px; align-items:center;}
  .ph-guide .g-cta span{font-size:12.5px; color:var(--text-dim);}
  @media (max-width:560px){ .ph-guide .g-cta .btn{width:100%; text-align:center;} }

  .wall{max-width:1120px; margin:0 auto; padding:0 24px 72px; columns:3 260px; column-gap:12px;}
  .wall button{display:block; width:100%; margin:0 0 12px; padding:0; border:0; background:var(--panel-2); border-radius:12px; overflow:hidden; cursor:zoom-in; break-inside:avoid;}
  .wall img{display:block; width:100%; height:auto; transition:transform .25s ease, opacity .25s ease;}
  .wall button:hover img{transform:scale(1.03);}
  .wall button:focus-visible{outline:3px solid var(--pink); outline-offset:2px;}
  .wall button{position:relative;}
  .wall button.is-video::after{content:"▶"; position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
    width:52px; height:52px; border-radius:50%; background:rgba(20,12,18,0.55); color:#fff; font-size:20px; line-height:52px; text-align:center;
    padding-left:4px; box-sizing:border-box; backdrop-filter:blur(3px); pointer-events:none;}

  .lb{position:fixed; inset:0; z-index:50; background:rgba(16,10,14,0.94); display:none; flex-direction:column; align-items:center; justify-content:center; padding:24px;}
  .lb.on{display:flex;}
  .lb img, .lb video{max-width:min(1600px,100%); max-height:calc(100vh - 120px); object-fit:contain; border-radius:6px; box-shadow:0 20px 60px rgba(0,0,0,0.5);}
  .lb [hidden]{display:none;}
  .lb .ig{margin-top:10px; font:600 13px Inter,sans-serif; color:#fff; text-decoration:none; padding:7px 14px; border-radius:999px;
    background:linear-gradient(135deg,#C22A66,#8E3A73);}
  .lb .ig:hover{opacity:.9;}
  .lb .cap{color:#f2e9ee; font-size:14px; margin-top:12px; text-align:center; min-height:1.4em;}
  .lb .cap small{display:block; font-family:'JetBrains Mono',monospace; font-size:11px; opacity:0.7; margin-top:2px;}
  .lb .nav{position:absolute; top:50%; transform:translateY(-50%); width:48px; height:48px; border-radius:50%; border:0; cursor:pointer;
    background:rgba(255,255,255,0.14); color:#fff; font-size:22px;}
  .lb .nav:hover{background:rgba(255,255,255,0.26);}
  .lb .prev{left:16px;} .lb .next{right:16px;}
  .lb .close{position:absolute; top:14px; right:16px; width:42px; height:42px; border-radius:50%; border:0; cursor:pointer; background:rgba(255,255,255,0.14); color:#fff; font-size:20px;}
  .lb .pos{position:absolute; top:22px; left:20px; color:#cfc3ca; font-family:'JetBrains Mono',monospace; font-size:12px;}
  @media (max-width:560px){ .wall{columns:2; column-gap:8px; padding:0 12px 56px;} .wall button{margin-bottom:8px;} .lb .nav{display:none;} .ph-sub .btn{margin-left:0;} }
</style>
@endpush

@section('content')
<header class="ph-head">
  <a class="ph-back" href="{{ route('gallery') }}">← All places</a>
  <h1><span class="gtext">{{ $place['name'] }}</span></h1>
  <div class="ph-sub">
    @php $nVid = collect($place['photos'])->where('type', 'video')->count(); $nPh = count($place['photos']) - $nVid; @endphp
    <span>{{ $place['country'] }}@if($span) · {{ $span }}@endif · {{ $nPh }} {{ \Illuminate\Support\Str::plural('photo', $nPh) }}@if($nVid) · {{ $nVid }} {{ \Illuminate\Support\Str::plural('video', $nVid) }}@endif</span>
    <a class="btn {{ $guide ? 'btn-ghost' : 'btn-primary' }}" href="{{ route('trips.create', ['destination' => $place['name'] . ($place['country'] ? ', ' . $place['country'] : '')]) }}">Plan a trip here →</a>
  </div>
  @if ($guide || $visa)
    <div class="ph-guide">
      @if ($guide)
        <div class="g-facts">
          <span><b>Best time</b>{{ $guide['best_season'] }}</span>
          <span><b>Ideal trip</b>{{ $guide['trip_length'] }}</span>
          @if ($visa)<span><b>Philippine passport</b>{{ \App\Support\Destinations::visaLabel($visa['visa_status']) }}</span>@endif
        </div>
        <p>{{ $guide['overview'] }}</p>
      @elseif ($visa)
        <div class="g-facts"><span><b>Philippine passport</b>{{ \App\Support\Destinations::visaLabel($visa['visa_status']) }}</span></div>
      @endif
      <div class="g-cta">
        <a class="btn btn-primary" href="{{ route('trips.create', ['destination' => $place['name'] . ($place['country'] ? ', ' . $place['country'] : '')]) }}">Plan my {{ $place['name'] }} trip — free</a>
        <span>Free while in beta · a day-by-day plan your group shapes together</span>
      </div>
    </div>
  @endif
</header>

<div class="wall" id="wall">
  @foreach($place['photos'] as $i => $p)
    <button type="button" data-i="{{ $i }}" class="{{ $p['type'] === 'video' ? 'is-video' : '' }}"
            aria-label="Open {{ $p['type'] === 'video' ? 'video' : 'photo' }} {{ $i + 1 }}{{ $p['caption'] ? ' — ' . $p['caption'] : '' }}">
      <img src="{{ Gallery::thumb($place['slug'], $p) }}" alt="{{ $p['caption'] ?: $place['name'] }}"
           @if($p['w'] && $p['h']) width="{{ $p['w'] }}" height="{{ $p['h'] }}" @endif loading="lazy" decoding="async">
    </button>
  @endforeach
</div>

<div class="lb" id="lb" role="dialog" aria-modal="true" aria-label="Photo viewer">
  <span class="pos" id="lbPos"></span>
  <button type="button" class="close" id="lbClose" aria-label="Close">✕</button>
  <button type="button" class="nav prev" id="lbPrev" aria-label="Previous photo">‹</button>
  <img id="lbImg" alt="">
  <video id="lbVid" controls playsinline preload="metadata" hidden></video>
  <div class="cap" id="lbCap"></div>
  <a class="ig" id="lbIg" href="#" target="_blank" rel="noopener noreferrer" hidden>View on Instagram ↗</a>
  <button type="button" class="nav next" id="lbNext" aria-label="Next photo">›</button>
</div>

<script>
(function () {
  var slides = @json($slides);
  var vid = document.getElementById('lbVid');
  var lb = document.getElementById('lb'), img = document.getElementById('lbImg'), cap = document.getElementById('lbCap'), pos = document.getElementById('lbPos');
  var i = 0, opener = null;

  function show(n) {
    i = (n + slides.length) % slides.length;
    var s = slides[i];
    vid.pause();
    if (s.type === 'video') {
      img.hidden = true; img.removeAttribute('src');
      vid.hidden = false; vid.poster = s.poster || ''; vid.src = s.src;
    } else {
      vid.hidden = true; vid.removeAttribute('src'); vid.load();
      img.hidden = false; img.src = s.src;
    }
    img.alt = s.caption || '';
    cap.textContent = s.caption || '';
    if (s.taken) { var sm = document.createElement('small'); sm.textContent = s.taken; cap.appendChild(sm); }
    pos.textContent = (i + 1) + ' / ' + slides.length;
    var ig = document.getElementById('lbIg');
    if (s.link) { ig.href = s.link; ig.hidden = false; } else { ig.hidden = true; ig.removeAttribute('href'); }
    // warm the next one
    var nx = slides[(i + 1) % slides.length];
    if (nx.type !== 'video') { var pre = new Image(); pre.src = nx.src; }
  }
  function open(n, el) { opener = el; show(n); lb.classList.add('on'); document.body.style.overflow = 'hidden'; document.getElementById('lbClose').focus(); }
  function close() { lb.classList.remove('on'); img.src = ''; vid.pause(); vid.removeAttribute('src'); vid.load(); document.body.style.overflow = ''; if (opener) opener.focus(); }

  document.getElementById('wall').addEventListener('click', function (e) {
    var b = e.target.closest('button[data-i]'); if (b) open(+b.dataset.i, b);
  });
  document.getElementById('lbClose').addEventListener('click', close);
  document.getElementById('lbPrev').addEventListener('click', function () { show(i - 1); });
  document.getElementById('lbNext').addEventListener('click', function () { show(i + 1); });
  lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
  document.addEventListener('keydown', function (e) {
    if (!lb.classList.contains('on')) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') show(i - 1);
    else if (e.key === 'ArrowRight') show(i + 1);
  });
  var x0 = null;
  lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  lb.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1));
  });
})();
</script>
@endsection
