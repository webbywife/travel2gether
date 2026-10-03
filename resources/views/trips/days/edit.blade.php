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
          <label class="f">Day title *</label>
          <input class="in" name="title" value="{{ old('title', $day->title) }}" required>
        </div>
        <div>
          <label class="f">Secondary title</label>
          <input class="in" name="title_secondary" value="{{ old('title_secondary', $day->title_secondary) }}" placeholder="optional, e.g. local-language name">
        </div>
        <div>
          <label class="f">Area label</label>
          <input class="in" name="area_label" value="{{ old('area_label', $day->area_label) }}" placeholder="shown under the date tab">
        </div>
        <div></div>
        <div>
          <label class="f">Hotel name for this day</label>
          <input class="in" name="hotel_name" value="{{ old('hotel_name', $day->hotel_name) }}" placeholder="leave blank to use the trip's main hotel">
        </div>
        <div>
          <label class="f">Hotel address for this day</label>
          <input class="in" name="hotel_address" value="{{ old('hotel_address', $day->hotel_address) }}">
        </div>
      </div>
      <div style="margin-top:12px;">
        <label class="f">Summary — "Why it's worth it"</label>
        <textarea class="in" name="summary">{{ old('summary', $day->summary) }}</textarea>
      </div>
      <div style="margin-top:12px;">
        <label class="f">Weather note</label>
        <textarea class="in" name="weather_note">{{ old('weather_note', $day->weather_note) }}</textarea>
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
@endsection
