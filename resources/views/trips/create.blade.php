@extends('layouts.site')

@section('title', 'New trip · Travel2gether')

@push('styles')
<style>
  .build{max-width:780px; margin:40px auto 0; padding:0 24px;}
  .build h1{font-family:'Space Grotesk',sans-serif; font-size:30px; margin:0 0 6px; letter-spacing:-0.015em;}
  .build .lede{color:var(--text-dim); margin:0 0 26px;}
  .step{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.94); box-shadow:var(--shadow-sm); padding:24px; margin-bottom:18px;}
  .step > h2{font-family:'Space Grotesk',sans-serif; font-size:17px; margin:0 0 4px; letter-spacing:-0.01em;}
  .step > h2 .n{font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--pink); margin-right:8px;}
  .step > .hint{color:var(--text-dim); font-size:13px; margin:0 0 16px;}
  .grid{display:grid; gap:12px;}
  .g2{grid-template-columns:1fr 1fr;}
  .g3{grid-template-columns:1fr 1fr 1fr;}
  label.f{display:block; font-size:12.5px; color:var(--text-dim); margin-bottom:4px; letter-spacing:0.02em;}
  .in{width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:var(--panel); font:inherit; font-size:14.5px; color:var(--text);}
  .in:focus{outline:2px solid var(--pink-light); border-color:var(--pink);}
  .flight{border:1px dashed var(--line); border-radius:12px; padding:14px; margin-bottom:12px;}
  .flight h4{margin:0 0 10px; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-dim);}
  .ps{position:relative;}
  .ps-menu{position:absolute; z-index:30; left:0; right:0; top:calc(100% + 4px); background:#fff; border:1px solid var(--line); border-radius:10px; box-shadow:var(--shadow-md); overflow:hidden; display:none;}
  .ps-menu.on{display:block;}
  .ps-menu button{display:block; width:100%; text-align:left; padding:9px 12px; border:0; background:none; font:inherit; font-size:13.5px; cursor:pointer;}
  .ps-menu button:hover{background:var(--panel-2);}
  .ps-menu .addr{display:block; color:var(--text-dim); font-size:11.5px;}
  .area-row{display:flex; gap:8px; align-items:flex-start; margin-bottom:8px;}
  .area-row .ps{flex:1;}
  .icon-btn{border:1px solid var(--line); background:#fff; border-radius:10px; width:38px; height:38px; font-size:16px; cursor:pointer; color:var(--text-dim); flex-shrink:0;}
  .icon-btn:hover{border-color:var(--pink); color:var(--pink);}
  .tags{display:flex; flex-wrap:wrap; gap:8px;}
  .tag-check{display:inline-flex; align-items:center; gap:7px; border:1px solid var(--line); border-radius:999px; padding:7px 13px; font-size:13px; cursor:pointer; background:#fff;}
  .tag-check input{accent-color:var(--pink);}
  .tag-check:has(input:checked){border-color:var(--pink); background:var(--panel-2); color:var(--text);}
  .shop-row{display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0; border-bottom:1px solid var(--line); font-size:14px;}
  .shop-row:last-child{border-bottom:0;}
  .shop-row .amt{display:flex; align-items:center; gap:5px;}
  .shop-row .amt input{width:92px; padding:7px 9px; border:1px solid var(--line); border-radius:8px; font:inherit; font-size:13.5px; text-align:right; background:var(--panel);}
  .shop-row .amt input:disabled{opacity:0.4;}
  .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 14px; font-size:13.5px; margin-bottom:18px;}
  .form-error ul{margin:0; padding-left:18px;}
  .actions{display:flex; gap:12px; align-items:center; margin:6px 0 60px;}
  @media (max-width:620px){ .g2,.g3{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="build">
  <h1><span class="gtext">Plan a new trip</span></h1>
  <p class="lede">Give the bones — flights, dates, where you're staying, the areas you want. You'll get an editable day-by-day skeleton to fill in (or draft with AI) and share with your group.</p>

  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @unless ($placesEnabled)
    <div class="form-error" style="background:var(--panel-2);border-color:var(--line);color:var(--text-dim);">
      Place search isn't configured yet — type hotel and area names by hand for now.
    </div>
  @endunless

  <form method="POST" action="{{ route('trips.store') }}" id="tripForm">
    @csrf

    <div class="step">
      <h2><span class="n">01</span>The trip</h2>
      <p class="hint">Not sure where? <a href="{{ route('destinations') }}">Browse popular destinations →</a></p>
      <div class="grid g2">
        <div>
          <label class="f">Destination *</label>
          <input class="in" id="destinationInput" name="destination" value="{{ old('destination', $prefillDestination ?? '') }}" placeholder="e.g. Kyoto, Japan" required>
          <div id="visaHint" style="display:none; margin-top:7px; font-size:12px;"></div>
        </div>
        <div><label class="f">Trip name</label><input class="in" name="title" value="{{ old('title') }}" placeholder="optional — defaults to “Kyoto trip”"></div>
        <div><label class="f">Travellers</label><input class="in" type="number" name="party_size" value="{{ old('party_size', 2) }}" min="1" max="20"></div>
        <div><label class="f">Currency</label>
          <select class="in" name="currency">
            @foreach (['USD','PHP','EUR','JPY','KRW','GBP','SGD','AUD'] as $c)
              <option value="{{ $c }}" @selected(old('currency','USD')===$c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="step">
      <h2><span class="n">02</span>Dates &amp; flights</h2>
      <p class="hint">Your itinerary runs from arrival to departure, one day per date.</p>
      <div class="grid g2" style="margin-bottom:14px;">
        <div><label class="f">Arrival date *</label><input class="in" type="date" name="arrival_date" value="{{ old('arrival_date') }}" required></div>
        <div><label class="f">Departure date *</label><input class="in" type="date" name="departure_date" value="{{ old('departure_date') }}" required></div>
      </div>

      @foreach (['Outbound flight', 'Return flight'] as $s => $lbl)
        <div class="flight">
          <h4>{{ $lbl }}</h4>
          <div class="grid g3">
            <div><label class="f">From (airport)</label><input class="in" name="segments[{{ $s }}][from]" value="{{ old("segments.$s.from") }}" placeholder="MNL"></div>
            <div><label class="f">To (airport)</label><input class="in" name="segments[{{ $s }}][to]" value="{{ old("segments.$s.to") }}" placeholder="KIX"></div>
            <div><label class="f">Date shown</label><input class="in" name="segments[{{ $s }}][date]" value="{{ old("segments.$s.date") }}" placeholder="WED 09 SEP 2026"></div>
            <div><label class="f">Depart</label><input class="in" name="segments[{{ $s }}][depart]" value="{{ old("segments.$s.depart") }}" placeholder="23:30"></div>
            <div><label class="f">Arrive</label><input class="in" name="segments[{{ $s }}][arrive]" value="{{ old("segments.$s.arrive") }}" placeholder="04:45 +1"></div>
            <div><label class="f">Terminal</label><input class="in" name="segments[{{ $s }}][terminal]" value="{{ old("segments.$s.terminal") }}" placeholder="T1 → T3"></div>
            <div><label class="f">Airline</label><input class="in" name="segments[{{ $s }}][airline]" value="{{ old("segments.$s.airline") }}" placeholder="Philippine Airlines"></div>
            <div><label class="f">Flight no.</label><input class="in" name="segments[{{ $s }}][flight_no]" value="{{ old("segments.$s.flight_no") }}" placeholder="PR 408"></div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="step">
      <h2><span class="n">03</span>Where you're staying</h2>
      <p class="hint">Search for the hotel — the day skeleton anchors bag-drop and checkout to it, and the map uses its location.</p>
      <div class="ps" data-place>
        <label class="f">Hotel</label>
        <input class="in" name="hotel_name" value="{{ old('hotel_name') }}" placeholder="Search a hotel or area…" autocomplete="off" data-place-input>
        <div class="ps-menu" data-place-menu></div>
        <input type="hidden" name="hotel_address" value="{{ old('hotel_address') }}" data-place-address>
        <input type="hidden" name="hotel_lat" value="{{ old('hotel_lat') }}" data-place-lat>
        <input type="hidden" name="hotel_lon" value="{{ old('hotel_lon') }}" data-place-lon>
      </div>
    </div>

    <div class="step">
      <h2><span class="n">04</span>Areas to visit</h2>
      <p class="hint">One area per line — a neighbourhood, a day-trip town, a district. They get spread across your days; extras become options. Search to pin a location so that day's weather and map are right.</p>
      <div id="areaList">
        @php $olds = old('areas', [['name'=>'']]); @endphp
        @foreach ($olds as $i => $a)
          <div class="area-row">
            <div class="ps" data-place>
              <input class="in" name="areas[{{ $i }}][name]" value="{{ $a['name'] ?? '' }}" placeholder="e.g. Higashiyama, or “Nara day trip”" autocomplete="off" data-place-input>
              <div class="ps-menu" data-place-menu></div>
              <input type="hidden" name="areas[{{ $i }}][lat]" value="{{ $a['lat'] ?? '' }}" data-place-lat>
              <input type="hidden" name="areas[{{ $i }}][lon]" value="{{ $a['lon'] ?? '' }}" data-place-lon>
            </div>
            <button type="button" class="icon-btn" data-remove-area>&times;</button>
          </div>
        @endforeach
      </div>
      <button type="button" class="btn btn-ghost" id="addArea" style="font-size:13px;padding:8px 16px;">+ Add area</button>
    </div>

    <div class="step">
      <h2><span class="n">05</span>What you're into</h2>
      <p class="hint">Used when the AI drafts each day.</p>
      <div class="tags">
        @foreach (['Food & drink','Temples & shrines','Museums & art','Nature & hiking','Beaches','Nightlife','Local markets','Architecture','Photography','History','Family-friendly','Relaxed pace'] as $int)
          <label class="tag-check"><input type="checkbox" name="interests[]" value="{{ $int }}" @checked(in_array($int, old('interests', [])))> {{ $int }}</label>
        @endforeach
      </div>
    </div>

    <div class="step">
      <h2><span class="n">06</span>Budget &amp; shopping</h2>
      <p class="hint">Rough numbers are fine — they seed the budget worksheet, which stays editable.</p>
      <div style="max-width:280px; margin-bottom:16px;">
        <label class="f">Target budget per person ({{ old('currency','USD') }})</label>
        <input class="in" type="number" name="budget_per_person" value="{{ old('budget_per_person') }}" min="0" placeholder="optional">
      </div>
      <p class="hint" style="margin-bottom:6px;">Planning to shop? Tick what for, and a rough spend:</p>
      @foreach (['electronics'=>'Electronics','stationery'=>'Stationery','clothes'=>'Clothing','beauty'=>'Cosmetics & beauty','food'=>'Food & edible souvenirs','homeware'=>'Homeware','souvenirs'=>'Souvenirs & gifts'] as $key => $lbl)
        <div class="shop-row">
          <label class="tag-check" style="border:0;padding:0;background:none;">
            <input type="checkbox" class="shop-toggle" data-key="{{ $key }}" @checked(old("shopping.$key") !== null)> {{ $lbl }}
          </label>
          <span class="amt"><span style="color:var(--text-dim);font-size:13px;">approx</span>
            <input type="number" name="shopping[{{ $key }}]" value="{{ old("shopping.$key") }}" min="0" placeholder="0" {{ old("shopping.$key") === null ? 'disabled' : '' }}></span>
        </div>
      @endforeach
    </div>

    <div class="actions">
      <button type="submit" class="btn btn-primary btn-lg">Create the trip</button>
      <a href="{{ route('dashboard') }}" style="font-size:13.5px;color:var(--text-dim);">Cancel</a>
    </div>
  </form>
</div>

<script>
(function () {
  // ---- repeatable areas ----
  var list = document.getElementById('areaList');
  document.getElementById('addArea').addEventListener('click', function () {
    var i = list.querySelectorAll('.area-row').length;
    var row = document.createElement('div');
    row.className = 'area-row';
    row.innerHTML =
      '<div class="ps" data-place>' +
        '<input class="in" name="areas[' + i + '][name]" placeholder="Another area…" autocomplete="off" data-place-input>' +
        '<div class="ps-menu" data-place-menu></div>' +
        '<input type="hidden" name="areas[' + i + '][lat]" data-place-lat>' +
        '<input type="hidden" name="areas[' + i + '][lon]" data-place-lon>' +
      '</div>' +
      '<button type="button" class="icon-btn" data-remove-area>&times;</button>';
    list.appendChild(row);
    attachPlace(row.querySelector('[data-place]'));
  });
  list.addEventListener('click', function (e) {
    if (e.target.matches('[data-remove-area]') && list.querySelectorAll('.area-row').length > 1) {
      e.target.closest('.area-row').remove();
    }
  });

  // ---- shopping amount enable/disable ----
  document.querySelectorAll('.shop-toggle').forEach(function (cb) {
    var amt = cb.closest('.shop-row').querySelector('.amt input');
    cb.addEventListener('change', function () { amt.disabled = !cb.checked; if (!cb.checked) amt.value = ''; });
  });

  // ---- place search ----
  var placesEnabled = @json($placesEnabled);
  function debounce(fn, ms) { var t; return function () { clearTimeout(t); var a = arguments, c = this; t = setTimeout(function () { fn.apply(c, a); }, ms); }; }

  function attachPlace(wrap) {
    if (!placesEnabled || !wrap) return;
    var input = wrap.querySelector('[data-place-input]');
    var menu = wrap.querySelector('[data-place-menu]');
    var latF = wrap.querySelector('[data-place-lat]');
    var lonF = wrap.querySelector('[data-place-lon]');
    var addrF = wrap.querySelector('[data-place-address]');
    if (!input) return;

    var run = debounce(function () {
      var q = input.value.trim();
      latF && (latF.value = ''); lonF && (lonF.value = ''); addrF && (addrF.value = '');
      if (q.length < 3) { menu.classList.remove('on'); return; }
      var hotelLat = document.querySelector('[name="hotel_lat"]');
      var near = hotelLat && hotelLat.value ? '&lat=' + hotelLat.value + '&lon=' + document.querySelector('[name="hotel_lon"]').value : '';
      fetch('/places/search?q=' + encodeURIComponent(q) + near, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
        .then(function (d) {
          menu.innerHTML = '';
          (d.results || []).slice(0, 6).forEach(function (p) {
            var b = document.createElement('button');
            b.type = 'button';
            b.innerHTML = '<strong>' + p.name + '</strong>' + (p.formatted_address ? '<span class="addr">' + p.formatted_address + '</span>' : '');
            b.addEventListener('click', function () {
              input.value = p.name;
              if (latF) latF.value = p.lat || '';
              if (lonF) lonF.value = p.lon || '';
              if (addrF) addrF.value = p.formatted_address || '';
              menu.classList.remove('on');
            });
            menu.appendChild(b);
          });
          menu.classList.toggle('on', menu.children.length > 0);
        })
        .catch(function () { menu.classList.remove('on'); });
    }, 280);

    input.addEventListener('input', run);
    input.addEventListener('blur', function () { setTimeout(function () { menu.classList.remove('on'); }, 150); });
  }

  document.querySelectorAll('[data-place]').forEach(attachPlace);

  // ---- visa hint ----
  var DESTS = @json($destinations ?? []);
  var destInput = document.getElementById('destinationInput'), visaHint = document.getElementById('visaHint');
  var visaColor = { visa_free: '#2f6d54', evisa: '#5a4488', required: '#9E4A6E' };
  var visaLabel = { visa_free: 'Visa-free', evisa: 'e-Visa', required: 'Visa required' };
  function checkVisa() {
    var q = destInput.value.trim().toLowerCase();
    if (!q) { visaHint.style.display = 'none'; return; }
    var hit = DESTS.find(function (d) {
      return q.indexOf(d.country.toLowerCase()) !== -1 || d.cities.some(function (c) { return q.indexOf(c.toLowerCase().split(' (')[0]) !== -1; });
    });
    if (!hit) { visaHint.style.display = 'none'; return; }
    visaHint.style.color = visaColor[hit.visa_status] || '#6B5860';
    visaHint.innerHTML = '🛂 <strong>' + (visaLabel[hit.visa_status] || 'Check visa') + '</strong> for a Philippine passport — ' + hit.visa_note;
    visaHint.style.display = 'block';
  }
  if (destInput) { destInput.addEventListener('input', checkVisa); checkVisa(); }
})();
</script>
@endsection
