{{-- Decorative travel doodles that drift on scroll. Purely cosmetic. --}}
<div class="parallax-root" id="parallaxRoot" aria-hidden="true">

  {{-- hot air balloon (colour illustration; outline draws itself in, then floats) --}}
  <div class="p balloon balloon-colour" data-speed="0.12" style="top:8vh; left:4%; width:130px; height:170px;">
    <span class="pi"><svg class="balloon-mark" viewBox="0 0 260 340" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <clipPath id="balloonClip">
          <path d="M130 35 C180 35 215 65 218 110 C221 155 205 195 178 224 C165 238 152 250 140 260 L118 260 C106 250 93 238 80 224 C53 195 37 155 40 110 C43 65 80 35 130 35 Z"/>
        </clipPath>
      </defs>
      <g class="balloon-rig">
        <path d="M130 35 C180 35 215 65 218 110 C221 155 205 195 178 224 C165 238 152 250 140 260 L118 260 C106 250 93 238 80 224 C53 195 37 155 40 110 C43 65 80 35 130 35 Z" fill="var(--cream)"/>
        <g clip-path="url(#balloonClip)">
          <rect x="36" y="32" width="190" height="230" fill="rgba(246,169,198,0.35)"/>
          <path d="M42 138 L78 116 L108 140 L130 114 L152 140 L182 116 L220 138 L220 180 L182 158 L152 184 L130 158 L108 184 L78 158 L42 180 Z" fill="var(--teal)" opacity=".9"/>
        </g>
        <path class="draw-path" d="M130 35 C180 35 215 65 218 110 C221 155 205 195 178 224 C165 238 152 250 140 260 L118 260 C106 250 93 238 80 224 C53 195 37 155 40 110 C43 65 80 35 130 35 Z" fill="none" stroke="var(--ink)" stroke-width="2.2"/>
        <path d="M130 37 L130 259" stroke="var(--ink)" stroke-width="1.4" opacity=".7"/>
        <path d="M130 37 C95 55 78 90 74 130 C70 168 82 208 100 240 C107 249 112 254 118 259" fill="none" stroke="var(--ink)" stroke-width="1.4" opacity=".7"/>
        <path d="M130 37 C165 55 182 90 186 130 C190 168 178 208 160 240 C153 249 148 254 142 259" fill="none" stroke="var(--ink)" stroke-width="1.4" opacity=".7"/>
        <circle cx="130" cy="35" r="6" fill="var(--teal)" stroke="none"/>
        <line x1="120" y1="258" x2="105" y2="286" stroke="var(--ink)" stroke-width=".9" opacity=".55"/>
        <line x1="140" y1="258" x2="155" y2="286" stroke="var(--ink)" stroke-width=".9" opacity=".55"/>
        <line x1="120" y1="258" x2="155" y2="286" stroke="var(--ink)" stroke-width=".7" opacity=".35"/>
        <line x1="140" y1="258" x2="105" y2="286" stroke="var(--ink)" stroke-width=".7" opacity=".35"/>
        <rect x="105" y="286" width="50" height="40" rx="4" fill="var(--green)" opacity=".9" stroke="var(--ink)" stroke-width="1.8"/>
        <path d="M105 296h50M105 306h50M105 316h50" stroke="rgba(255,255,255,.55)" stroke-width="1"/>
      </g>
    </svg></span>
  </div>

  {{-- airplane (top view, climbing, with a dashed trail) --}}
  <div class="p" data-speed="0.26" style="top:14vh; right:6%; width:160px; height:118px;">
    <span class="pi"><svg viewBox="0 0 150 110" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 100 C 28 96, 46 84, 60 70" stroke-dasharray="3 6"/>
      <g transform="translate(98 44) rotate(50) scale(1.2) translate(-30 -40)">
        <path d="M30 2c3 0 5 5 5 12v18l26 15v7l-26-8v20l8 7v6l-13-4-13 4v-6l8-7V46L-1 54v-7l26-15V14c0-7 2-12 5-12Z"/>
        <path d="M25 18h10"/>
      </g>
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
