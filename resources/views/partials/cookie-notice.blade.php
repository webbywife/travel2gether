<div id="cookieNotice" class="cookie-notice" role="status" aria-live="polite" hidden>
  <p>
    We use essential cookies to keep you signed in — no ad trackers. Your trip picks and Trippie chat
    stay in your browser's local storage, not on our servers.
    <a href="{{ route('privacy') }}">Learn more</a>
  </p>
  <button type="button" id="cookieOk">Got it</button>
</div>

<style>
  .cookie-notice{
    position:fixed; left:16px; right:16px; bottom:16px; z-index:11000; max-width:560px; margin:0 auto;
    display:flex; align-items:center; gap:14px; flex-wrap:wrap;
    background:#241E23; color:#fff; border-radius:14px; padding:14px 16px;
    box-shadow:0 20px 50px -18px rgba(0,0,0,0.5); font-family:'Inter',system-ui,sans-serif;
    animation:cn-up .3s ease both;
  }
  .cookie-notice p{margin:0; font-size:13px; line-height:1.55; flex:1; min-width:220px;}
  .cookie-notice a{color:#F6A9C6; text-decoration:underline;}
  .cookie-notice button{
    border:0; border-radius:999px; padding:9px 16px; font-size:13px; font-weight:700; cursor:pointer;
    color:#fff; background:linear-gradient(135deg,#C22A66,#7156A8); flex-shrink:0;
  }
  @keyframes cn-up{from{transform:translateY(16px); opacity:0;} to{transform:none; opacity:1;}}
  @media (prefers-reduced-motion: reduce){ .cookie-notice{animation:none;} }
</style>

<script>
(function () {
  var KEY = 't2g:cookie-notice-ack';
  var el = document.getElementById('cookieNotice');
  if (!el) return;
  try {
    if (!localStorage.getItem(KEY)) el.hidden = false;
  } catch (e) {
    el.hidden = false; // if storage is blocked, still show it — just won't remember dismissal
  }
  document.getElementById('cookieOk').addEventListener('click', function () {
    el.hidden = true;
    try { localStorage.setItem(KEY, '1'); } catch (e) {}
  });
})();
</script>
