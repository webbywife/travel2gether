@extends('layouts.site')

@section('title', 'Edit Day ' . $day->day_number . ' · ' . $trip->title)

@push('styles')
<style>
  .build{max-width:640px; margin:40px auto 0; padding:0 24px;}
  .build h1{font-family:'Space Grotesk',sans-serif; font-size:28px; margin:0 0 6px; letter-spacing:-0.015em;}
  .build .lede{color:var(--text-dim); margin:0 0 26px;}
  .step{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.94); box-shadow:var(--shadow-sm); padding:24px; margin-bottom:18px;}
  .grid{display:grid; gap:12px;}
  .g2{grid-template-columns:1fr 1fr;}
  label.f{display:block; font-size:12.5px; color:var(--text-dim); margin-bottom:4px; letter-spacing:0.02em;}
  .in{width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:var(--panel); font:inherit; font-size:14.5px; color:var(--text);}
  .in:focus{outline:2px solid var(--pink-light); border-color:var(--pink);}
  textarea.in{resize:vertical; min-height:70px; font-family:inherit;}
  .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 14px; font-size:13.5px; margin-bottom:18px;}
  .form-error ul{margin:0; padding-left:18px;}
  .actions{display:flex; gap:12px; align-items:center; margin:6px 0 60px;}
  @media (max-width:620px){ .g2{grid-template-columns:1fr;} }

  /* 📍 place search */
  .ps{position:relative;}
  .ps-menu{display:none; position:absolute; left:0; right:0; top:calc(100% - 22px); z-index:20; background:#fff; border:1px solid var(--line);
    border-radius:12px; box-shadow:var(--shadow-md); max-height:260px; overflow:auto;}
  .ps-menu.on{display:block;}
  .ps-menu button{display:block; width:100%; text-align:left; border:0; background:none; padding:9px 12px; font:inherit; font-size:14px; cursor:pointer;}
  .ps-menu button:hover, .ps-menu button:focus{background:var(--panel-2);}
  .ps-menu .addr{display:block; font-size:12px; color:var(--text-dim);}
  .loc-status{font-size:12.5px; margin:5px 0 0; color:#2f6d54;}
  .loc-status.warn{color:var(--accent);}
  .loc-err{font-size:12.5px; margin:4px 0 0; color:var(--pink); font-weight:600;}
  .ps-menu .cat{display:inline-block; font-size:11px; font-weight:600; color:var(--lavender); background:rgba(113,86,168,.1); border-radius:999px; padding:1px 7px; margin-left:6px; vertical-align:1px;}
  .near{margin-top:16px; border:1px solid var(--line); border-radius:14px; padding:14px 16px; background:var(--panel);}
  .near h3{font-family:'Space Grotesk',sans-serif; font-size:15px; margin:0 0 10px;}
  .near-cols{display:grid; grid-template-columns:repeat(3,1fr); gap:14px;}
  .near h4{font-size:12.5px; margin:0 0 6px; color:var(--text-dim); font-weight:600;}
  .near h4 small{font-weight:400;}
  .near ul{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:4px;}
  .near li{font-size:13px; line-height:1.35;}
  .near li .km{color:var(--text-dim); font-size:12px; white-space:nowrap;}
  .near li button{all:unset; cursor:pointer; color:var(--text); border-bottom:1px dashed var(--pink-light);}
  .near li button:hover, .near li button:focus-visible{color:var(--pink); border-bottom-color:var(--pink);}
  .near li a{color:var(--text); text-decoration:none; border-bottom:1px dotted var(--line);}
  .near li a:hover{color:var(--pink);}
  .near-note{font-size:12px; color:var(--text-dim); margin:8px 0 0;}
  @media (max-width:720px){ .near-cols{grid-template-columns:1fr;} }

  /* ✨ AI helper */
  .ai-help{border:1px solid rgba(113,86,168,0.28); background:linear-gradient(180deg, rgba(253,234,241,0.65), rgba(255,255,255,0.94));}
  .ai-help h2{font-family:'Space Grotesk',sans-serif; font-size:17px; margin:0 0 4px;}
  .ai-help p{margin:0 0 12px; font-size:13.5px; color:var(--text-dim);}
  .ai-row{display:flex; gap:10px; flex-wrap:wrap;}
  .ai-row .in{flex:1; min-width:200px;}
  .ai-status{font-size:13px; color:var(--text-dim); margin-top:10px; min-height:1.2em;}
  .ai-status.err{color:var(--pink);}
  .ai-note{margin-top:12px; padding:10px 12px; border-radius:10px; background:rgba(113,86,168,0.08); font-size:13.5px;}
  .ai-actions{display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;}
  .sugg{margin-top:6px; padding:8px 10px; border-radius:10px; background:rgba(253,234,241,0.7); border:1px dashed var(--pink-light);
    font-size:13px; display:flex; gap:10px; align-items:flex-start; justify-content:space-between;}
  .sugg span{flex:1;} .sugg b{font-weight:600; color:var(--accent); font-size:11.5px; display:block; letter-spacing:0.03em;}
  .sugg button{border:0; background:var(--grad-soft); color:#fff; font:600 12px Inter,sans-serif; padding:6px 11px; border-radius:999px; cursor:pointer; white-space:nowrap;}
  .sugg.used{opacity:0.5;} .sugg.used button{background:#9a8f96;}
</style>
@endpush

@section('content')
<div class="build">
  <h1><span class="gtext">Edit Day {{ $day->day_number }}</span></h1>
  <p class="lede">Title, area, hotel, and notes for this day. Stops and their options stay as-is here — edit those by re-drafting the day with AI.</p>

  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @if($aiHelper)
  <div class="step ai-help" id="aiHelp">
    <h2>✨ Help me with this day</h2>
    <p>I'll look at your whole trip and suggest a title, area, summary and weather note for Day {{ $day->day_number }} — nothing changes until you click <b>Use this</b> and save.</p>
    <div class="ai-row">
      <input class="in" id="aiWish" maxlength="200" placeholder="Optional: what should this day be about? e.g. Muir Woods and Sausalito">
      <button type="button" class="btn btn-primary" id="aiGo">✨ Suggest</button>
    </div>
    <div class="ai-status" id="aiStatus" aria-live="polite"></div>
    <div class="ai-note" id="aiDup" hidden></div>
    <div class="ai-note" id="aiHotel" hidden></div>
    <div class="ai-actions" id="aiActions" hidden>
      <button type="button" class="btn btn-ghost" id="aiAll">Use all suggestions</button>
      <button type="button" class="btn btn-ghost" id="aiAgain">↻ Try different ideas</button>
    </div>
  </div>
  @endif

  <form method="POST" action="{{ route('trips.days.update', [$trip, $day]) }}">
    @csrf
    @method('PATCH')

    <div class="step">
      <div class="grid g2">
        <div>
          <label class="f" for="f-title">Day title *</label>
          <input id="f-title" class="in" name="title" value="{{ old('title', $day->title) }}" required>
        </div>
        <div>
          <label class="f" for="f-title-secondary">Secondary title</label>
          <input id="f-title-secondary" class="in" name="title_secondary" value="{{ old('title_secondary', $day->title_secondary) }}" placeholder="optional, e.g. local-language name">
        </div>
        <div class="ps" data-loc data-kind="area">
          <label class="f" for="f-area-label">Area for this day</label>
          <input class="in" id="f-area-label" name="area_label" value="{{ old('area_label', $day->area_label) }}"
                 placeholder="Search a neighbourhood, town or park…" autocomplete="off" data-loc-input
                 data-was="{{ $day->area_label }}" aria-describedby="area-status">
          <div class="ps-menu" data-loc-menu></div>
          <input type="hidden" name="lat" value="{{ old('lat') }}" data-loc-lat>
          <input type="hidden" name="lon" value="{{ old('lon') }}" data-loc-lon>
          <p class="loc-status" id="area-status" data-loc-status data-located="{{ ($day->lat && $day->lon) ? '1' : '' }}" data-empty="Optional — the day uses the trip's location">@if($day->area_label && $day->lat && $day->lon)📍 Located on the map @elseif($day->area_label)⚠ Not located yet — search and pick a suggestion @else Optional — the day uses the trip's location @endif</p>
          @error('area_label')<p class="loc-err">{{ $message }}</p>@enderror
        </div>
        <div></div>
        <div class="ps" data-loc data-kind="hotel">
          <label class="f" for="f-hotel-name">Hotel for this day</label>
          <input class="in" id="f-hotel-name" name="hotel_name" value="{{ old('hotel_name', $day->hotel_name) }}"
                 placeholder="Search your hotel — blank uses the trip's main hotel" autocomplete="off" data-loc-input
                 data-was="{{ $day->hotel_name }}" aria-describedby="hotel-status">
          <div class="ps-menu" data-loc-menu></div>
          <input type="hidden" name="hotel_lat" value="{{ old('hotel_lat') }}" data-loc-lat>
          <input type="hidden" name="hotel_lon" value="{{ old('hotel_lon') }}" data-loc-lon>
          <input type="hidden" name="hotel_address" value="{{ old('hotel_address', $day->hotel_address) }}" data-loc-address>
          <p class="loc-status" id="hotel-status" data-loc-status data-located="{{ ($day->hotel_lat && $day->hotel_lon) ? '1' : '' }}" data-empty="Blank — uses the trip's main hotel">@if($day->hotel_name && $day->hotel_lat && $day->hotel_lon)📍 {{ $day->hotel_address ?: 'Located on the map' }}@elseif($day->hotel_name)⚠ Not located yet — search and pick a suggestion @else Blank — uses the trip's main hotel @endif</p>
          @error('hotel_name')<p class="loc-err">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="near" id="nearPanel" hidden aria-live="polite">
        <h3 id="nearTitle">Near this area</h3>
        <div class="near-cols">
          <section><h4>✈️ Airports</h4><ul id="nearAirports"></ul></section>
          <section><h4>🏨 Hotels nearby <small>tap to use</small></h4><ul id="nearHotels"></ul></section>
          <section><h4>📍 Landmarks &amp; things to see</h4><ul id="nearLandmarks"></ul></section>
        </div>
        <p class="near-note" id="nearNote"></p>
      </div>
      <div style="margin-top:12px;">
        <label class="f" for="f-summary">Summary — "Why it's worth it"</label>
        <textarea id="f-summary" class="in" name="summary">{{ old('summary', $day->summary) }}</textarea>
      </div>
      <div style="margin-top:12px;">
        <label class="f" for="f-weather-note">Weather note</label>
        <textarea id="f-weather-note" class="in" name="weather_note">{{ old('weather_note', $day->weather_note) }}</textarea>
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn btn-primary">Save changes</button>
      <a href="{{ route('trips.show', $trip) }}#{{ $day->day_number }}" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

@if($aiHelper)
<script>
(function () {
  var url = @json(route('trips.days.suggest', [$trip, $day])), csrf = @json(csrf_token());
  var FIELDS = { title: 'Day title', title_secondary: 'Secondary title', area_label: 'Area label', summary: 'Summary', weather_note: 'Weather note' };
  var go = document.getElementById('aiGo'), again = document.getElementById('aiAgain'), all = document.getElementById('aiAll');
  var status = document.getElementById('aiStatus'), actions = document.getElementById('aiActions');
  var dup = document.getElementById('aiDup'), hotel = document.getElementById('aiHotel');
  var seen = [], last = null;

  function field(name) { return document.querySelector('[name="' + name + '"]'); }
  function clear() { document.querySelectorAll('.sugg').forEach(function (n) { n.remove(); }); }

  function useIt(name, value, box) {
    var f = field(name); if (!f) return;
    f.value = value; f.dispatchEvent(new Event('input'));
    box.classList.add('used'); box.querySelector('button').textContent = 'Used ✓';
  }

  function show(data) {
    clear(); last = data;
    Object.keys(FIELDS).forEach(function (name) {
      var v = (data[name] || '').trim(), f = field(name);
      if (!v || !f || v === f.value.trim()) return;
      var box = document.createElement('div'); box.className = 'sugg';
      var t = document.createElement('span'), b = document.createElement('b');
      b.textContent = '✨ Suggestion'; t.appendChild(b); t.appendChild(document.createTextNode(v));
      var btn = document.createElement('button'); btn.type = 'button'; btn.textContent = 'Use this';
      btn.addEventListener('click', function () { useIt(name, v, box); });
      box.appendChild(t); box.appendChild(btn);
      f.insertAdjacentElement('afterend', box);
    });
    dup.hidden = !data.duplicate_note; dup.textContent = data.duplicate_note ? '🔁 ' + data.duplicate_note : '';
    hotel.hidden = !data.hotel_hint; hotel.textContent = data.hotel_hint ? '🏨 ' + data.hotel_hint : '';
    if (data.title) seen.push(data.title);
    actions.hidden = false;
    status.className = 'ai-status';
    status.textContent = document.querySelectorAll('.sugg').length
      ? 'Suggestions are under each field below.'
      : 'Your current details already look good — try a different idea if you like.';
  }

  async function ask() {
    go.disabled = again.disabled = true;
    status.className = 'ai-status'; status.textContent = '✨ Thinking about your trip…';
    try {
      var res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ wish: document.getElementById('aiWish').value.trim() || null, avoid: seen.slice(-6) }),
      });
      var data = await res.json().catch(function () { return {}; });
      if (!res.ok) throw new Error(data.message || (res.status === 429 ? 'Slow down a little — try again in a minute.' : 'Something went wrong.'));
      show(data);
    } catch (e) {
      status.className = 'ai-status err'; status.textContent = e.message;
    } finally {
      go.disabled = again.disabled = false;
    }
  }

  go.addEventListener('click', ask);
  again.addEventListener('click', ask);
  all.addEventListener('click', function () {
    document.querySelectorAll('.sugg:not(.used) button').forEach(function (b) { b.click(); });
  });
  document.getElementById('aiWish').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); ask(); } });
})();
</script>
@endif
<script>
/* 📍 Area & hotel must be real places (with coordinates) — search, then pick. */
(function () {
  var near = @json(['lat' => $day->lat ?? $trip->lat, 'lon' => $day->lon ?? $trip->lon]);
  var searchUrl = @json(route('places.search'));

  document.querySelectorAll('[data-loc]').forEach(function (wrap) {
    var input = wrap.querySelector('[data-loc-input]'), menu = wrap.querySelector('[data-loc-menu]');
    var lat = wrap.querySelector('[data-loc-lat]'), lon = wrap.querySelector('[data-loc-lon]');
    var addr = wrap.querySelector('[data-loc-address]'), status = wrap.querySelector('[data-loc-status]');
    var was = (input.dataset.was || '').trim(), wasLocated = status.dataset.located === '1';
    var timer = null, seq = 0;

    function setStatus(text, warn) { status.textContent = text; status.classList.toggle('warn', !!warn); }
    function located() { return lat.value !== '' && lon.value !== ''; }
    wrap.isOk = function () {
      var v = input.value.trim();
      return v === '' || located() || (v === was && wasLocated);
    };

    function refreshStatus() {
      var v = input.value.trim();
      if (v === '') setStatus(status.dataset.empty, false);
      else if (located()) return;
      else if (v === was && wasLocated) setStatus('📍 Located on the map', false);
      else setStatus('⚠ Not located yet — pick one of the suggestions', true);
    }

    function pick(p) {
      input.value = p.name;
      lat.value = p.lat; lon.value = p.lon;
      if (addr) addr.value = p.formatted_address || '';
      menu.classList.remove('on'); menu.textContent = '';
      setStatus('📍 ' + p.name + (p.formatted_address ? ' — ' + p.formatted_address : ''), false);
      if (wrap.dataset.kind === 'area') { window.t2gExplore && window.t2gExplore(p.lat, p.lon, p.name); }
    }
    wrap.pick = pick;

    function search() {
      var q = input.value.trim(), my = ++seq;
      if (q.length < 3) { menu.classList.remove('on'); return; }
      var url = searchUrl + '?q=' + encodeURIComponent(q) + '&kind=' + (wrap.dataset.kind || '') + (near.lat ? '&lat=' + near.lat + '&lon=' + near.lon : '');
      fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
        .then(function (d) {
          if (my !== seq) return; // a newer search is on its way
          menu.textContent = '';
          (d.results || []).filter(function (p) { return p.lat != null && p.lon != null; }).slice(0, 6).forEach(function (p) {
            var b = document.createElement('button'); b.type = 'button';
            var n = document.createElement('strong'); n.textContent = p.name; b.appendChild(n);
            if (p.category) { var c = document.createElement('span'); c.className = 'cat'; c.textContent = p.category; b.appendChild(c); }
            if (p.formatted_address) { var a = document.createElement('span'); a.className = 'addr'; a.textContent = p.formatted_address; b.appendChild(a); }
            b.addEventListener('mousedown', function (e) { e.preventDefault(); pick(p); });
            b.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); pick(p); } });
            menu.appendChild(b);
          });
          if (!menu.children.length) {
            var none = document.createElement('div'); none.style.cssText = 'padding:9px 12px; font-size:13px; color:var(--text-dim)';
            none.textContent = 'No matching place found — try a nearby landmark or the town name.'; menu.appendChild(none);
          }
          menu.classList.add('on');
        })
        .catch(function () { menu.classList.remove('on'); });
    }

    input.addEventListener('input', function () {
      lat.value = ''; lon.value = '';
      if (addr && input.value.trim() !== was) addr.value = '';
      refreshStatus();
      clearTimeout(timer); timer = setTimeout(search, 300);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { var f = menu.querySelector('button'); if (f) { e.preventDefault(); f.focus(); } }
      if (e.key === 'Escape') menu.classList.remove('on');
    });
    input.addEventListener('blur', function () { setTimeout(function () { if (!wrap.contains(document.activeElement)) menu.classList.remove('on'); }, 150); });
  });

  // ── "Near this area": airports, hotels (tap to use) and landmarks ──
  var exploreUrl = @json(route('places.explore'));
  var panel = document.getElementById('nearPanel');
  function li(listId, item, onPick) {
    var el = document.createElement('li'), label;
    if (onPick) {
      label = document.createElement('button'); label.type = 'button'; label.textContent = item.name;
      label.addEventListener('click', function () { onPick(item); });
    } else {
      label = document.createElement('a'); label.target = '_blank'; label.rel = 'noopener noreferrer';
      label.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(item.name + ' ' + (item.formatted_address || ''));
      label.textContent = item.name;
    }
    el.appendChild(label);
    var km = document.createElement('span'); km.className = 'km'; km.textContent = ' · ' + item.km + ' km'; el.appendChild(km);
    document.getElementById(listId).appendChild(el);
  }
  window.t2gExplore = function (lat, lon, name) {
    if (lat == null || lon == null) return;
    panel.hidden = false;
    document.getElementById('nearTitle').textContent = 'Near ' + (name || 'this area');
    ['nearAirports', 'nearHotels', 'nearLandmarks'].forEach(function (id) { document.getElementById(id).textContent = ''; });
    var note = document.getElementById('nearNote'); note.textContent = 'Looking around…';
    fetch(exploreUrl + '?lat=' + lat + '&lon=' + lon, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) { note.textContent = 'Nearby places are unavailable right now — try again shortly.'; return; }
        var hotelWrap = document.querySelector('[data-loc][data-kind="hotel"]');
        (d.airports || []).forEach(function (a) { li('nearAirports', a); });
        (d.hotels || []).forEach(function (h) { li('nearHotels', h, function (p) { hotelWrap.pick(p); hotelWrap.querySelector('[data-loc-input]').focus(); }); });
        (d.landmarks || []).forEach(function (l) { li('nearLandmarks', l); });
        ['nearAirports', 'nearHotels', 'nearLandmarks'].forEach(function (id) {
          var ul = document.getElementById(id);
          if (!ul.children.length) { var e = document.createElement('li'); e.className = 'km'; e.textContent = 'None found nearby'; ul.appendChild(e); }
        });
        note.textContent = d.partial ? 'Hotels and landmarks are temporarily unavailable — airports are shown.' : 'From OpenStreetMap and OurAirports · distances are straight-line.';
      })
      .catch(function () { note.textContent = 'Nearby places are unavailable right now.'; });
  };
  @if($day->lat && $day->lon)
  window.t2gExplore({{ (float) $day->lat }}, {{ (float) $day->lon }}, @json($day->area_label ?: $day->title));
  @endif

  // Don't let an unlocated area/hotel be saved.
  var form = document.querySelector('form[action*="/days/"]');
  form && form.addEventListener('submit', function (e) {
    var bad = Array.from(document.querySelectorAll('[data-loc]')).filter(function (w) { return !w.isOk(); });
    if (bad.length) {
      e.preventDefault();
      bad.forEach(function (w) { var s = w.querySelector('[data-loc-status]'); s.textContent = '⚠ Please pick this place from the suggestions before saving'; s.classList.add('warn'); });
      bad[0].querySelector('[data-loc-input]').focus();
    }
  });
})();
</script>
@endsection
