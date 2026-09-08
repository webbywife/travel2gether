@if(config('services.gemini.api_key'))
<style>
  #trippie, #trippie *{box-sizing:border-box;}
  #trippie{position:fixed; right:20px; bottom:20px; z-index:12000; font-family:'Inter',system-ui,sans-serif;}
  #trippie .t-launch{
    display:flex; align-items:center; gap:9px; border:0; cursor:pointer; padding:11px 16px 11px 12px;
    border-radius:999px; color:#fff; font-size:14px; font-weight:700; letter-spacing:-0.01em;
    background:linear-gradient(120deg,#C22A66,#8E3A73 55%,#6E54A6);
    box-shadow:0 12px 30px -10px rgba(120,40,80,0.6);
    animation:t-pop .5s cubic-bezier(.22,1.2,.36,1) both;
  }
  #trippie .t-launch:hover{transform:translateY(-2px);}
  #trippie .t-launch .t-face{width:30px; height:30px; display:grid; place-items:center; background:rgba(255,255,255,0.25); border-radius:50%; overflow:hidden;}
  #trippie .t-launch .t-face img{width:26px; height:26px; object-fit:contain;}
  #trippie .t-panel{
    position:absolute; right:0; bottom:60px; width:min(380px, calc(100vw - 32px)); height:min(560px, calc(100vh - 120px));
    background:#fff; border:1px solid rgba(36,30,35,0.12); border-radius:20px; overflow:hidden;
    box-shadow:0 30px 70px -20px rgba(36,30,35,0.4); display:none; flex-direction:column;
    animation:t-slide .28s ease both;
  }
  #trippie.open .t-panel{display:flex;}
  #trippie.open .t-launch{display:none;}
  #trippie .t-head{display:flex; align-items:center; gap:10px; padding:14px 14px; color:#fff;
    background:linear-gradient(120deg,#C22A66,#8E3A73 55%,#6E54A6);}
  #trippie .t-head .t-av{width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,0.25); display:grid; place-items:center; overflow:hidden;}
  #trippie .t-head .t-av img{width:34px; height:34px; object-fit:contain;}
  #trippie .t-head .t-nm{font-weight:800; font-size:15px; line-height:1.1;}
  #trippie .t-head .t-sub{font-size:11px; opacity:0.85;}
  #trippie .t-head .t-x{margin-left:auto; background:none; border:0; color:#fff; font-size:20px; cursor:pointer; opacity:0.85; line-height:1;}
  #trippie .t-body{flex:1; overflow-y:auto; padding:14px; background:#FFF8FA; display:flex; flex-direction:column; gap:12px;}
  #trippie .t-msg{max-width:82%; font-size:13.5px; line-height:1.5; padding:9px 12px; border-radius:14px;}
  #trippie .t-msg.me{align-self:flex-end; background:linear-gradient(135deg,#C22A66,#8E3A73); color:#fff; border-bottom-right-radius:4px;}
  #trippie .t-botwrap{align-self:flex-start; display:flex; align-items:flex-end; gap:7px; max-width:92%;}
  #trippie .t-botwrap .t-emo{width:38px; height:38px; flex-shrink:0; object-fit:contain; filter:drop-shadow(0 2px 3px rgba(120,40,80,0.2)); animation:t-bob 3s ease-in-out infinite;}
  #trippie .t-msg.bot{background:#fff; border:1px solid rgba(36,30,35,0.1); color:#241E23; border-bottom-left-radius:4px;}
  @keyframes t-bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}
  #trippie .t-msg.bot strong{color:#C22A66;}
  #trippie .t-msg.bot ul{margin:6px 0 0; padding-left:18px;}
  #trippie .t-msg.bot li{margin-bottom:3px;}
  #trippie .t-typing{align-self:flex-start; display:flex; gap:4px; padding:11px 13px; background:#fff; border:1px solid rgba(36,30,35,0.1); border-radius:14px;}
  #trippie .t-typing i{width:6px; height:6px; border-radius:50%; background:#C77DA2; animation:t-blink 1s infinite;}
  #trippie .t-typing i:nth-child(2){animation-delay:.15s;} #trippie .t-typing i:nth-child(3){animation-delay:.3s;}
  #trippie .t-chips{display:flex; flex-wrap:wrap; gap:6px; padding:0 14px 8px; background:#FFF8FA;}
  #trippie .t-chips button{font-size:12px; padding:6px 11px; border-radius:999px; border:1px solid rgba(36,30,35,0.12); background:#fff; color:#6B5860; cursor:pointer;}
  #trippie .t-chips button:hover{border-color:#F6A9C6; color:#241E23;}
  #trippie .t-cta{display:block; text-align:center; text-decoration:none; font-size:12.5px; font-weight:700;
    color:#fff; padding:10px 12px; background:linear-gradient(120deg,#C22A66,#8E3A73 55%,#6E54A6);}
  #trippie .t-cta:hover{filter:brightness(1.06);}
  #trippie .t-in{display:flex; gap:8px; padding:12px 12px 14px; border-top:1px solid rgba(36,30,35,0.1); background:#fff;}
  #trippie .t-in input{flex:1; border:1px solid rgba(36,30,35,0.14); border-radius:999px; padding:10px 14px; font:inherit; font-size:13.5px; outline:none;}
  #trippie .t-in input:focus{border-color:#C22A66;}
  #trippie .t-in button{border:0; border-radius:50%; width:38px; height:38px; cursor:pointer; color:#fff; background:linear-gradient(135deg,#C22A66,#7156A8); font-size:16px;}
  #trippie .t-in button:disabled{opacity:0.5; cursor:default;}
  @keyframes t-pop{from{transform:scale(.4); opacity:0;} to{transform:scale(1); opacity:1;}}
  @keyframes t-slide{from{transform:translateY(12px); opacity:0;} to{transform:none; opacity:1;}}
  @keyframes t-blink{0%,60%,100%{opacity:.3; transform:translateY(0);} 30%{opacity:1; transform:translateY(-3px);}}
  @media (prefers-reduced-motion: reduce){ #trippie .t-launch, #trippie .t-panel{animation:none;} #trippie .t-typing i, #trippie .t-botwrap .t-emo{animation:none;} }
</style>

@php $t_emo = asset('img/trippie'); @endphp

<div id="trippie" aria-live="polite" data-emo-base="{{ $t_emo }}">
  <button class="t-launch" type="button" data-t-open>
    <span class="t-face"><img src="{{ $t_emo }}/happy.png" alt=""></span> Ask Trippie
  </button>

  <div class="t-panel" role="dialog" aria-label="Chat with Trippie">
    <div class="t-head">
      <span class="t-av"><img src="{{ $t_emo }}/excited.png" alt=""></span>
      <span><span class="t-nm">Trippie</span><br><span class="t-sub">your planning buddy</span></span>
      <button class="t-x" type="button" data-t-close aria-label="Close">&times;</button>
    </div>
    <div class="t-body" id="tBody">
      <div class="t-botwrap"><img class="t-emo" src="{{ $t_emo }}/happy.png" alt="Trippie"><div class="t-msg bot">Hey! I'm Trippie 🧳 Tell me where you're thinking of going — I'll help you shape it, then we'll build the day-by-day together.</div></div>
    </div>
    <div class="t-chips" id="tChips">
      <button type="button">Where should I go for 5 days?</button>
      <button type="button">How many days for Tokyo?</button>
      <button type="button">Best area to stay in Kyoto?</button>
      <button type="button">Help me plan a beach trip</button>
    </div>
    <a class="t-cta" href="{{ auth()->check() ? route('trips.create') : route('register') }}">✨ Ready? Let's build your trip →</a>
    <form class="t-in" id="tForm">
      <input type="text" id="tInput" placeholder="Ask Trippie…" autocomplete="off" maxlength="1000" required>
      <button type="submit" id="tSend" aria-label="Send">➤</button>
    </form>
  </div>
</div>

<script>
(function () {
  var root = document.getElementById('trippie');
  if (!root) return;
  var body = document.getElementById('tBody'), chips = document.getElementById('tChips');
  var form = document.getElementById('tForm'), input = document.getElementById('tInput'), send = document.getElementById('tSend');
  var csrf = @json(csrf_token());
  var tripSlug = @json($trip->slug ?? '');
  var EMO = root.dataset.emoBase;
  var EMOS = ['happy','excited','thinking','idea','confused','worried','sad'];
  var KEY = 'trippie:history';
  var history = [];
  try { history = JSON.parse(sessionStorage.getItem(KEY)) || []; } catch (e) {}

  function fmt(s) {
    var esc = s.replace(/[&<>]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]; });
    esc = esc.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    var lines = esc.split('\n'), out = '', inList = false;
    lines.forEach(function (ln) {
      var m = ln.match(/^\s*[-*]\s+(.*)/);
      if (m) { if (!inList) { out += '<ul>'; inList = true; } out += '<li>' + m[1] + '</li>'; }
      else { if (inList) { out += '</ul>'; inList = false; } if (ln.trim()) out += (out ? '<br>' : '') + ln; }
    });
    if (inList) out += '</ul>';
    return out;
  }
  function add(role, text, emotion) {
    if (role === 'me') {
      var d = document.createElement('div');
      d.className = 't-msg me';
      d.textContent = text;
      body.appendChild(d);
    } else {
      var emo = EMOS.indexOf(emotion) >= 0 ? emotion : 'happy';
      var wrap = document.createElement('div');
      wrap.className = 't-botwrap';
      wrap.innerHTML = '<img class="t-emo" src="' + EMO + '/' + emo + '.png" alt="Trippie" onerror="this.src=\'' + EMO + '/happy.png\'">' +
        '<div class="t-msg bot">' + fmt(text) + '</div>';
      body.appendChild(wrap);
    }
    body.scrollTop = body.scrollHeight;
  }
  function typing(on) {
    var e = document.getElementById('tTy');
    if (on && !e) { var t = document.createElement('div'); t.id = 'tTy'; t.className = 't-typing'; t.innerHTML = '<i></i><i></i><i></i>'; body.appendChild(t); body.scrollTop = body.scrollHeight; }
    if (!on && e) e.remove();
  }
  history.forEach(function (h) { add(h.role === 'model' ? 'bot' : 'me', h.text, h.emotion); });

  function ask(message) {
    add('me', message);
    history.push({ role: 'user', text: message });
    chips.style.display = 'none';
    input.value = ''; input.disabled = true; send.disabled = true; typing(true);

    fetch('/trippie', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify({ message: message, history: history.slice(0, -1).slice(-12), trip_slug: tripSlug || null }),
    })
      .then(function (r) { return r.ok ? r.json() : { reply: r.status === 429 ? "Whoa, slow down a sec — ask me again in a moment 😄" : "I couldn't reach my maps just now — try again shortly.", emotion: 'worried' }; })
      .then(function (d) {
        typing(false);
        add('bot', d.reply, d.emotion);
        history.push({ role: 'model', text: d.reply, emotion: d.emotion || 'happy' });
        try { sessionStorage.setItem(KEY, JSON.stringify(history.slice(-20))); } catch (e) {}
      })
      .catch(function () { typing(false); add('bot', "Hmm, connection hiccup — try that again? 🧭", 'confused'); })
      .finally(function () { input.disabled = false; send.disabled = false; input.focus(); });
  }

  root.querySelector('[data-t-open]').addEventListener('click', function () { root.classList.add('open'); input.focus(); });
  root.querySelector('[data-t-close]').addEventListener('click', function () { root.classList.remove('open'); });
  form.addEventListener('submit', function (e) { e.preventDefault(); var v = input.value.trim(); if (v) ask(v); });
  chips.querySelectorAll('button').forEach(function (b) { b.addEventListener('click', function () { ask(b.textContent); }); });
})();
</script>
@endif
