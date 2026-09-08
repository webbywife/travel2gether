{{-- Decorative travel doodles that drift on scroll. Purely cosmetic. --}}
<div class="parallax-root" id="parallaxRoot" aria-hidden="true">

  {{-- hot air balloon (brand mark) --}}
  <div class="p brand balloon" data-speed="0.12" style="top:8vh; left:4%; width:112px; height:150px;">
    <span class="pi"><img src="{{ asset('img/parallax/balloon.png') }}" alt=""></span>
  </div>

  {{-- airplane (brand mark) --}}
  <div class="p brand" data-speed="0.26" style="top:14vh; right:6%; width:150px; height:95px;">
    <span class="pi"><img src="{{ asset('img/parallax/plane.png') }}" alt=""></span>
  </div>

  {{-- train (brand mark) --}}
  <div class="p brand" data-speed="0.08" style="top:120vh; left:2%; width:180px; height:141px;">
    <span class="pi"><img src="{{ asset('img/parallax/train.png') }}" alt=""></span>
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

  {{-- boat (brand mark) --}}
  <div class="p brand ship" data-speed="-0.06" style="top:250vh; left:6%; width:170px; height:118px;">
    <span class="pi"><img src="{{ asset('img/parallax/boat.png') }}" alt=""></span>
  </div>

  {{-- compass --}}
  <div class="p" data-speed="0.32" style="top:70vh; left:44%; width:96px; height:96px; opacity:0.1;">
    <span class="pi"><svg viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="48" cy="48" r="40"/><circle cx="48" cy="48" r="4"/>
      <path d="M48 14l10 26-10 8-10-8 10-26ZM48 82l-10-26 10-8 10 8-10 26Z"/>
    </svg></span>
  </div>

  {{-- car (brand mark) --}}
  <div class="p brand" data-speed="0.22" style="top:300vh; right:12%; width:90px; height:133px;">
    <span class="pi"><img src="{{ asset('img/parallax/car.png') }}" alt=""></span>
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
