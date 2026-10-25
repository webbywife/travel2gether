@extends('layouts.site')

@section('title', 'Plan your trip, step by step · Travel2gether')

@push('styles')
<style>
  .guide{max-width:640px; margin:40px auto 0; padding:0 24px 56px;}
  .guide .hint{color:var(--text-dim); font-size:14px; margin:0 0 16px;}
  .guide label.f{display:block; font-size:13px; color:var(--text-dim); margin-bottom:6px; letter-spacing:0.02em;}
  .guide .in{width:100%; padding:13px 14px; border:1px solid var(--line); border-radius:12px; background:#fff; font:inherit; font-size:16px; color:var(--text); min-width:0; max-width:100%;}
  .guide .in:focus{outline:2px solid var(--pink-light); border-color:var(--pink);}
  .guide .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 14px; font-size:13.5px; margin-bottom:18px;}
  .guide .form-error ul{margin:0; padding-left:18px;}
  .guide .btn-lg{padding:14px 28px;}
  .g-progress{height:6px; border-radius:99px; background:var(--panel-2); overflow:hidden; margin:0 0 6px;}
  .g-progress i{display:block; height:100%; width:0; background:var(--pink); transition:width .25s ease;}
  .g-count{font-family:'IBM Plex Mono',monospace; font-size:12px; color:var(--text-dim); margin:0 0 22px;}
  .gstep h1{font-family:'Inter Tight',sans-serif; font-size:28px; margin:0 0 6px; letter-spacing:-0.015em;}
  .gstep .hint{color:var(--text-dim); margin:0 0 18px; font-size:14px;}
  .opts{display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:6px;}
  .opt-card{display:block; border:1px solid var(--line); border-radius:14px; padding:14px 16px; background:#fff; cursor:pointer; font-size:14px;}
  .opt-card b{display:block; font-size:15px; margin-bottom:2px;}
  .opt-card small{color:var(--text-dim);}
  .opt-card input{position:absolute; opacity:0; pointer-events:none;}
  .opt-card:has(input:checked){border-color:var(--pink); background:var(--panel-2); box-shadow:0 0 0 1px var(--pink) inset;}
  .opt-card:has(input:focus-visible){outline:2px solid var(--pink); outline-offset:2px;}
  .chips{display:flex; flex-wrap:wrap; gap:8px;}
  .chips .opt-card{padding:8px 14px; border-radius:999px; font-size:13.5px;}
  .g-err{color:#9E4A6E; font-size:13.5px; min-height:20px; margin:8px 0 0;}
  .g-nav{display:flex; gap:12px; align-items:center; margin-top:22px;}
  .g-nav .back{background:none; border:0; color:var(--text-dim); font:inherit; cursor:pointer; padding:8px 4px;}
  .g-sum{border:1px solid var(--line); border-radius:14px; background:#fff; padding:6px 16px; margin-bottom:6px;}
  .g-sum div{display:flex; justify-content:space-between; gap:16px; padding:10px 0; border-bottom:1px solid var(--line); font-size:14px;}
  .g-sum div:last-child{border:0;} .g-sum span:first-child{color:var(--text-dim);}
  .g-alt{font-size:13px; color:var(--text-dim); margin-top:26px;}
  .stepper{display:flex; align-items:center; gap:12px; margin-top:12px;}
  .stepper button{width:38px; height:38px; border-radius:50%; border:1px solid var(--line); background:#fff; font-size:18px; cursor:pointer;}
  .stepper output{min-width:28px; text-align:center; font-weight:600; font-size:18px;}
  @media (prefers-reduced-motion:reduce){.g-progress i{transition:none;}}
</style>
@endpush

@section('content')
<div class="guide">
  <div class="g-progress" aria-hidden="true"><i id="gBar"></i></div>
  <p class="g-count" id="gCount" aria-live="polite">Step 1 of 7</p>

  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <p style="margin:8px 0 0;"><a href="{{ route('trips.create') }}">Use the full form instead →</a></p></div>
  @endif

  <form method="POST" action="{{ route('trips.store') }}" id="gForm" novalidate>
    @csrf
    <input type="hidden" name="currency" value="PHP">
    <input type="hidden" name="departure_date" id="gDeparture">
    <input type="hidden" name="party_size" id="gParty" value="2">
    <input type="hidden" name="segments[0][from]" id="gSeg0From" value=""><input type="hidden" name="segments[1][to]" id="gSeg1To" value="">

    <section class="gstep" data-step>
      <h1>Where are you going?</h1>
      <p class="hint">A city or country. Pick a suggestion or type your own.</p>
      <input class="in" id="gDest" name="destination" list="gDests" value="{{ old('destination', $prefillDestination) }}" placeholder="e.g. Kyoto, Japan" autocomplete="off" required>
      <datalist id="gDests">@foreach ($suggestions as $s)<option value="{{ $s }}">@endforeach</datalist>
      <label class="f" for="gFrom" style="margin-top:18px;">Starting from <span style="opacity:.7">(optional)</span></label>
      <input class="in" id="gFrom" list="gAirports" placeholder="e.g. Manila (MNL)" autocomplete="off" maxlength="60">
      <p class="hint" style="margin:6px 0 0; font-size:13px;">Your home airport or city. It goes on your flight pass. Skip it and the plan starts on arrival.</p>
      <datalist id="gAirports">@foreach ($airports as $a)<option value="{{ $a['city'] }} ({{ $a['code'] }})">{{ $a['name'] }}</option>@endforeach</datalist>
    </section>

    <section class="gstep" data-step hidden>
      <h1>When, and for how long?</h1>
      <p class="hint">Your plan gets one day per date. You can change this later.</p>
      <label class="f" for="gArrival">Arrival date</label>
      <input class="in" type="date" id="gArrival" name="arrival_date" value="{{ old('arrival_date') }}" min="{{ now()->toDateString() }}" required style="max-width:260px;">
      <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-top:16px; max-width:520px;">
        <div><label class="f" for="gLand">What time do you land? <span style="opacity:.7">(optional)</span></label>
          <input class="in" type="time" id="gLand" name="segments[0][arrive]"></div>
        <div><label class="f" for="gLeave">What time is your flight home? <span style="opacity:.7">(optional)</span></label>
          <input class="in" type="time" id="gLeave" name="segments[1][depart]"></div>
      </div>
      <p class="hint" style="margin:6px 0 0; font-size:13px;">Day 1 and your last day are planned around these. Not sure yet? Skip them and add them later.</p>
      <p class="hint" style="margin:18px 0 8px;">How many days?</p>
      <div class="chips">
        @foreach ([3,4,5,7,10] as $n)
          <label class="opt-card"><input type="radio" name="g_days" value="{{ $n }}" @checked($n === 5)> {{ $n }} days</label>
        @endforeach
      </div>
    </section>

    <section class="gstep" data-step hidden>
      <h1>Who's coming?</h1>
      <p class="hint">So the plan fits your group.</p>
      <div class="opts">
        <label class="opt-card"><input type="radio" name="who" value="Solo" data-n="1"><b>Solo</b><small>Just me</small></label>
        <label class="opt-card"><input type="radio" name="who" value="Couple" data-n="2" checked><b>Couple</b><small>Two of us</small></label>
        <label class="opt-card"><input type="radio" name="who" value="Friends" data-n="4"><b>Friends</b><small>A barkada trip</small></label>
        <label class="opt-card"><input type="radio" name="who" value="Family" data-n="4"><b>Family</b><small>With kids or parents</small></label>
      </div>
      <div class="stepper" role="group" aria-label="Number of travellers">
        <button type="button" id="gMinus" aria-label="Fewer travellers">−</button><output id="gPartyOut" aria-live="polite">2</output><button type="button" id="gPlus" aria-label="More travellers">+</button>
        <span class="hint" style="margin:0;">travellers</span>
      </div>
    </section>

    <section class="gstep" data-step hidden>
      <h1>How do you like to travel?</h1>
      <p class="hint">This sets how full each day is.</p>
      <div class="opts">
        <label class="opt-card"><input type="radio" name="pace" value="Relaxed"><b>Relaxed</b><small>2 things a day, time to wander</small></label>
        <label class="opt-card"><input type="radio" name="pace" value="Balanced" checked><b>Balanced</b><small>A mix of sights and downtime</small></label>
        <label class="opt-card"><input type="radio" name="pace" value="Packed"><b>Packed</b><small>See as much as possible</small></label>
      </div>
    </section>

    <section class="gstep" data-step hidden>
      <h1>What are you into?</h1>
      <p class="hint">Pick any. Skip it and we'll cover the highlights.</p>
      <div class="chips">
        @foreach (['Food & drink','Temples & shrines','Museums & art','Nature & hiking','Beaches','Nightlife','Local markets','Architecture','Photography','History','Family-friendly','Shopping'] as $int)
          <label class="opt-card"><input type="checkbox" name="interests[]" value="{{ $int }}" @checked(in_array($int, old('interests', [])))> {{ $int }}</label>
        @endforeach
      </div>
    </section>

    <section class="gstep" data-step hidden>
      <h1>Anything we should plan around?</h1>
      <p class="hint">Optional. Tick what applies to anyone in your group and we'll adjust the walking, rest stops and warnings. This is trip planning, not medical advice.</p>
      <div class="chips">
        @foreach (\App\Support\TravelNeeds::OPTIONS as $key => $opt)
          <label class="opt-card"><input type="checkbox" name="needs[]" value="{{ $key }}" @checked(in_array($key, old('needs', [])))> {{ $opt[0] }}</label>
        @endforeach
      </div>
      <p class="hint" style="margin:14px 0 0; font-size:13px;">Why we ask: only so the plan fits your group. We keep your ticks with this trip, not your name, and only people you invite can see it. Skip this step if you prefer.</p>
    </section>

    <section class="gstep" data-step hidden>
      <h1>Ready? Here's your trip.</h1>
      <p class="hint">What's your spending style? Then we'll build it.</p>
      <div class="opts" style="margin-bottom:18px;">
        <label class="opt-card"><input type="radio" name="spend" value="Budget-friendly"><b>Budget-friendly</b><small>Hostels, street food</small></label>
        <label class="opt-card"><input type="radio" name="spend" value="Mid-range" checked><b>Mid-range</b><small>Comfortable, good value</small></label>
        <label class="opt-card"><input type="radio" name="spend" value="Treat-yourself"><b>Treat yourself</b><small>Nice hotels, great meals</small></label>
      </div>
      <div class="g-sum" id="gSum" aria-live="polite"></div>
    </section>

    <p class="g-err" id="gErr" role="alert"></p>
    <div class="g-nav">
      <button type="button" class="back" id="gBack" hidden>← Back</button>
      <button type="button" class="btn btn-primary btn-lg" id="gNext">Next →</button>
    </div>
    @guest<p class="hint" style="margin-top:14px; font-size:13px;">No account needed to start. You'll sign up (free, 20 seconds) only to save the plan.</p>@endguest
  </form>
  <p class="g-alt">Want every detail (flights, hotel, areas, budget)? <a href="{{ route('trips.create') }}">Use the full form →</a></p>
</div>
<script>
(function () {
  var form = document.getElementById('gForm');
  var steps = [].slice.call(form.querySelectorAll('[data-step]'));
  var i = 0, party = 2;
  var $ = function (id) { return document.getElementById(id); };
  var checked = function (n) { var e = form.querySelector('input[name="' + n + '"]:checked'); return e ? e.value : ''; };

  function iso(d) { return d.toISOString().slice(0, 10); }
  function departure() {
    var a = $('gArrival').value; if (!a) return '';
    var d = new Date(a + 'T00:00:00Z'); d.setUTCDate(d.getUTCDate() + Math.max(1, parseInt(checked('g_days') || 5, 10) - 1));
    return iso(d);
  }
  function setParty(n) { party = Math.min(20, Math.max(1, n)); $('gParty').value = party; $('gPartyOut').textContent = party; }

  function validate() {
    if (i === 0 && !$('gDest').value.trim()) return 'Tell us where you\'re headed.';
    if (i === 1 && !$('gArrival').value) return 'Pick your arrival date.';
    if (i === 1 && $('gArrival').value < $('gArrival').min) return 'That date has passed. Pick a future date.';
    return '';
  }
  function summary() {
    var rows = [['Where', $('gDest').value], ['From', $('gFrom').value.trim() || 'Not set'], ['Dates', $('gArrival').value + ' → ' + departure()], ['Land / fly home', ($('gLand').value || '—') + ' / ' + ($('gLeave').value || '—')], ['Who', checked('who') + ' · ' + party],
      ['Pace', checked('pace')], ['Style', checked('spend')],
      ['Plan around', (function () { var n = form.querySelectorAll('input[name="needs[]"]:checked').length; return n ? n + ' selected' : 'Nothing'; })()]];
    var box = $('gSum'); box.textContent = '';
    rows.forEach(function (r) { var d = document.createElement('div'), a = document.createElement('span'), b = document.createElement('span'); a.textContent = r[0]; b.textContent = r[1]; d.appendChild(a); d.appendChild(b); box.appendChild(d); });
  }
  function show(n) {
    i = n;
    steps.forEach(function (s, k) { s.hidden = k !== i; });
    $('gBar').style.width = ((i + 1) / steps.length * 100) + '%';
    $('gCount').textContent = 'Step ' + (i + 1) + ' of ' + steps.length;
    $('gBack').hidden = i === 0;
    $('gNext').textContent = i === steps.length - 1 ? 'Create my trip' : 'Next →';
    $('gErr').textContent = '';
    if (i === steps.length - 1) summary();
    var f = steps[i].querySelector('input:not([type=hidden])'); if (f && i < 2) f.focus();
  }
  function submit() {
    $('gDeparture').value = departure();
    var from = $('gFrom').value.trim(), m = from.match(/\(([A-Za-z]{3})\)\s*$/);
    from = m ? m[1].toUpperCase() : from;
    $('gSeg0From').value = from; $('gSeg1To').value = from;
    form.submit();
  }
  $('gNext').addEventListener('click', function () {
    var err = validate(); $('gErr').textContent = err; if (err) return;
    i === steps.length - 1 ? submit() : show(i + 1);
  });
  $('gBack').addEventListener('click', function () { show(i - 1); });
  form.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') { e.preventDefault(); $('gNext').click(); } });
  form.querySelectorAll('input[name="who"]').forEach(function (r) { r.addEventListener('change', function () { setParty(parseInt(r.dataset.n, 10)); }); });
  $('gMinus').addEventListener('click', function () { setParty(party - 1); });
  $('gPlus').addEventListener('click', function () { setParty(party + 1); });
  show(0);
})();
</script>
@endsection
