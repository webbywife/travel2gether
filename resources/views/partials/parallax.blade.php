{{-- Decorative travel doodles that drift on scroll. Purely cosmetic. --}}
<div class="parallax-root" id="parallaxRoot" aria-hidden="true">

  {{-- hot air balloon --}}
  <div class="p balloon" data-speed="0.12" style="top:8vh; left:4%; width:120px; height:150px;">
    <span class="pi"><svg viewBox="0 0 120 150" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M60 6c26 0 44 20 44 46 0 24-20 42-30 54H46C36 94 16 76 16 52 16 26 34 6 60 6Z"/>
      <path d="M60 6c-14 0-22 22-22 48s8 46 22 58c14-12 22-32 22-58S74 6 60 6Z"/>
      <path d="M38 52c0 26 8 46 22 58 14-12 22-32 22-58"/>
      <path d="M50 106h20l4 12H46l4-12Z"/>
      <path d="M52 118v10a8 8 0 0 0 16 0v-10"/>
    </svg></span>
  </div>

  {{-- airplane --}}
  <div class="p" data-speed="0.26" style="top:14vh; right:6%; width:150px; height:80px;">
    <span class="pi"><svg viewBox="0 0 150 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 46c4-3 12-4 22-2l30 6 26-30c3-3 8-4 11-1s3 8 0 11L74 54l6 30c1 5-1 9-4 10s-7-1-9-5L52 62l-24 6c-6 2-11 1-13-3s0-16 11-19Z"/>
    </svg></span>
  </div>

  {{-- train --}}
  <div class="p" data-speed="0.08" style="top:120vh; left:2%; width:190px; height:100px;">
    <span class="pi"><svg viewBox="0 0 190 100" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 20h96c22 0 40 18 40 40v14H20a12 12 0 0 1-12-12V32a12 12 0 0 1 12-12Z"/>
      <path d="M32 34h28v22H32zM74 34h28v22H74z"/>
      <path d="M118 40h18c10 0 16 6 18 16h-36V40Z"/>
      <circle cx="46" cy="86" r="9"/><circle cx="96" cy="86" r="9"/><circle cx="140" cy="86" r="9"/>
      <path d="M8 74h150"/><path d="M96 20V8h14"/>
    </svg></span>
  </div>

  {{-- bus --}}
  <div class="p" data-speed="0.18" style="top:190vh; right:3%; width:180px; height:96px;">
    <span class="pi"><svg viewBox="0 0 180 96" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="10" y="14" width="150" height="58" rx="12"/>
      <path d="M28 30h20v18H28zM60 30h20v18H60zM92 30h20v18H92zM124 30h22v18h-22z"/>
      <path d="M10 56h150"/>
      <circle cx="46" cy="80" r="10"/><circle cx="128" cy="80" r="10"/>
      <path d="M160 34h10c6 0 8 6 8 14v10h-18"/>
    </svg></span>
  </div>

  {{-- ship --}}
  <div class="p ship" data-speed="-0.06" style="top:250vh; left:6%; width:170px; height:120px;">
    <span class="pi"><svg viewBox="0 0 170 120" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 82h142l-16 26a12 12 0 0 1-11 7H41a12 12 0 0 1-11-7L14 82Z"/>
      <path d="M84 14v66"/>
      <path d="M84 20l40 16-40 14V20Z"/>
      <path d="M84 30 44 44l40 12V30Z"/>
      <path d="M4 96c8 6 14 6 22 0s14-6 22 0 14 6 22 0 14-6 22 0 14 6 22 0 14-6 22 0"/>
    </svg></span>
  </div>

  {{-- compass --}}
  <div class="p" data-speed="0.32" style="top:70vh; left:44%; width:96px; height:96px; opacity:0.1;">
    <span class="pi"><svg viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="48" cy="48" r="40"/><circle cx="48" cy="48" r="4"/>
      <path d="M48 14l10 26-10 8-10-8 10-26ZM48 82l-10-26 10-8 10 8-10 26Z"/>
    </svg></span>
  </div>

  {{-- little plane 2 --}}
  <div class="p" data-speed="0.22" style="top:300vh; right:12%; width:110px; height:60px; opacity:0.12;">
    <span class="pi"><svg viewBox="0 0 110 60" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 34c3-2 9-3 16-1l22 4 18-22c2-2 6-3 8-1s2 6 0 8L52 40l4 20c1 4-1 7-3 7s-5-1-6-4L38 46l-18 4c-4 1-8 0-9-2s0-11 7-14Z"/>
    </svg></span>
  </div>

</div>

<script>
(function () {
  var root = document.getElementById('parallaxRoot');
  if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var items = Array.prototype.slice.call(root.querySelectorAll('.p'));
  var ticking = false;

  function apply() {
    var y = window.pageYOffset || document.documentElement.scrollTop || 0;
    for (var i = 0; i < items.length; i++) {
      var s = parseFloat(items[i].getAttribute('data-speed')) || 0;
      items[i].style.transform = 'translate3d(0,' + (y * s).toFixed(1) + 'px,0)';
    }
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) { window.requestAnimationFrame(apply); ticking = true; }
  }, { passive: true });

  apply();
})();
</script>
