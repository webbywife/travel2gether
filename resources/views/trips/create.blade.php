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
  .area-main{flex:1; min-width:0;}
  .area-distance{font-size:12px; color:var(--pink); margin:6px 0 0 2px;}
  .area-hotel{margin-top:6px;}
  .area-hotel summary{font-size:12px; color:var(--text-dim); cursor:pointer; list-style:none;}
  .area-hotel summary::-webkit-details-marker{display:none;}
  .area-hotel summary:hover{color:var(--pink);}
  .area-hotel[open] summary{color:var(--pink); margin-bottom:2px;}
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
  .start{background:var(--panel-2); border:1px solid var(--line); border-radius:12px; padding:12px 14px; margin:0 0 14px;}
  .start-head{font-weight:600; font-size:14px; margin-bottom:8px;}
  .start-note{font-size:12.5px; color:var(--text-dim); margin:4px 0;}
  .start-group{margin:8px 0;}
  .start-label{display:block; font-size:12px; color:var(--text-dim); margin-bottom:5px;}
  .start-chips{display:flex; flex-wrap:wrap; gap:6px;}
  .start-chips button{border:1px solid var(--line); background:#fff; border-radius:999px; padding:6px 11px; font:inherit; font-size:12.5px; cursor:pointer; text-align:left;}
  .start-chips button:hover{border-color:var(--pink);}
  .start-chips button small{color:var(--text-dim); margin-left:4px;}
</style>
@endpush

@section('content')
<div class="build">
  <h1><span class="gtext">Plan a new trip</span></h1>
  @guest
    <p class="lede" style="margin-bottom:8px;"><strong>No account needed to start.</strong> Fill it in first; you'll only sign up to save it.</p>
  @endguest
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
          <div class="ps" data-place data-kind="destination" id="destWrap">
            <label class="f" for="destinationInput">Destination *</label>
            <input class="in" id="destinationInput" name="destination" value="{{ old('destination', $prefillDestination ?? '') }}" placeholder="e.g. Kyoto, Japan" autocomplete="off" required data-place-input>
            <div class="ps-menu" data-place-menu></div>
            <input type="hidden" name="dest_lat" value="{{ old('dest_lat') }}" data-place-lat>
            <input type="hidden" name="dest_lon" value="{{ old('dest_lon') }}" data-place-lon>
          </div>
          <div id="visaHint" style="display:none; margin-top:7px; font-size:12px;"></div>
          <div id="templateHint" style="display:none; margin-top:7px; font-size:12px; color:var(--text-dim); background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:8px 11px;"></div>
        </div>
        <div><label class="f" for="f-title">Trip name</label><input id="f-title" class="in" name="title" value="{{ old('title') }}" placeholder="optional — defaults to “Kyoto trip”"></div>
        <div><label class="f" for="f-party-size">Travellers</label><input id="f-party-size" class="in" type="number" name="party_size" value="{{ old('party_size', 2) }}" min="1" max="20"></div>
        <div><label class="f" for="f-currency">Currency</label>
          <select id="f-currency" class="in" name="currency">
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
        <div><label class="f" for="f-arrival-date">Arrival date *</label><input id="f-arrival-date" class="in" type="date" name="arrival_date" value="{{ old('arrival_date') }}" required></div>
        <div><label class="f" for="f-departure-date">Departure date *</label><input id="f-departure-date" class="in" type="date" name="departure_date" value="{{ old('departure_date') }}" required></div>
      </div>

      @foreach (['Outbound flight', 'Return flight'] as $s => $lbl)
        <div class="flight">
          <h4>{{ $lbl }}</h4>
          <div class="grid g3">
            <div><label class="f" for="f-segments-{{ $s }}-from">From (airport)</label><input id="f-segments-{{ $s }}-from" class="in" list="airportsList" name="segments[{{ $s }}][from]" value="{{ old("segments.$s.from") }}" placeholder="MNL" autocomplete="off"></div>
            <div><label class="f" for="f-segments-{{ $s }}-to">To (airport)</label><input id="f-segments-{{ $s }}-to" class="in" list="airportsList" name="segments[{{ $s }}][to]" value="{{ old("segments.$s.to") }}" placeholder="KIX" autocomplete="off"></div>
            <div><label class="f" for="f-segments-{{ $s }}-depart">Depart</label><input id="f-segments-{{ $s }}-depart" class="in" name="segments[{{ $s }}][depart]" value="{{ old("segments.$s.depart") }}" placeholder="23:30"></div>
            <div><label class="f" for="f-segments-{{ $s }}-arrive">Arrive</label><input id="f-segments-{{ $s }}-arrive" class="in" name="segments[{{ $s }}][arrive]" value="{{ old("segments.$s.arrive") }}" placeholder="04:45 +1"></div>
            <div><label class="f" for="f-segments-{{ $s }}-terminal">Terminal</label><input id="f-segments-{{ $s }}-terminal" class="in" name="segments[{{ $s }}][terminal]" value="{{ old("segments.$s.terminal") }}" placeholder="T1 → T3"></div>
            <div><label class="f" for="f-segments-{{ $s }}-airline">Airline</label><input id="f-segments-{{ $s }}-airline" class="in" list="airlinesList" name="segments[{{ $s }}][airline]" value="{{ old("segments.$s.airline") }}" placeholder="Philippine Airlines" autocomplete="off"></div>
            <div><label class="f" for="f-segments-{{ $s }}-flight-no">Flight no.</label><input id="f-segments-{{ $s }}-flight-no" class="in" name="segments[{{ $s }}][flight_no]" value="{{ old("segments.$s.flight_no") }}" placeholder="PR 408"></div>
          </div>
          <p class="hint" style="margin:10px 0 0;">The date shown on the flight pass is filled in automatically from your arrival/departure dates above.</p>
        </div>
      @endforeach

      <datalist id="airportsList">
        @foreach ($airports ?? [] as $a)
          <option value="{{ $a['code'] }}">{{ $a['name'] }}, {{ $a['city'] }}</option>
        @endforeach
      </datalist>
      <datalist id="airlinesList">
        @foreach ($airlines ?? [] as $a)
          <option value="{{ $a['name'] }}">{{ $a['code'] }}</option>
        @endforeach
      </datalist>
    </div>

    <div class="step">
      <h2><span class="n">03</span>Where you're staying</h2>
      <p class="hint">Search for the hotel — the day skeleton anchors bag-drop and checkout to it, and the map uses its location.</p>
      <div class="start" id="startPanel" hidden>
        <div class="start-head" id="startHead"></div>
        <div class="start-group" id="startAirportsWrap" hidden><span class="start-label">✈️ Airports — tap the one you fly into</span><div class="start-chips" id="startAirports"></div></div>
        <div class="start-note" id="startAirport" hidden></div>
        <div class="start-group" id="startAreasWrap" hidden><span class="start-label">🏘️ Areas where visitors stay</span><div class="start-chips" id="startAreas"></div></div>
        <div class="start-group" id="startHotelsWrap" hidden><span class="start-label">🏨 Hotels &amp; hostels near the centre — tap to use</span><div class="start-chips" id="startHotels"></div></div>
        <div class="start-note" id="startStatus"></div>
      </div>
      <div class="ps" data-place data-kind="hotel">
        <label class="f" for="f-hotel-name">Hotel</label>
        <input id="f-hotel-name" class="in" name="hotel_name" value="{{ old('hotel_name') }}" placeholder="Search a hotel or area…" autocomplete="off" data-place-input>
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
            <div class="area-main">
              <div class="ps" data-place data-kind="area">
                <input class="in" name="areas[{{ $i }}][name]" value="{{ $a['name'] ?? '' }}" placeholder="e.g. Higashiyama, or “Nara day trip”" autocomplete="off" data-place-input>
                <div class="ps-menu" data-place-menu></div>
                <input type="hidden" name="areas[{{ $i }}][lat]" value="{{ $a['lat'] ?? '' }}" data-place-lat>
                <input type="hidden" name="areas[{{ $i }}][lon]" value="{{ $a['lon'] ?? '' }}" data-place-lon>
              </div>
              <div class="area-distance" data-area-distance style="display:none;"></div>
              <details class="area-hotel">
                <summary>+ Staying somewhere different for this area? (multi-city)</summary>
                <div class="ps" data-place style="margin-top:8px;">
                  <input class="in" name="areas[{{ $i }}][hotel_name]" value="{{ $a['hotel_name'] ?? '' }}" placeholder="Search a hotel for this area" autocomplete="off" data-place-input>
                  <div class="ps-menu" data-place-menu></div>
                  <input type="hidden" name="areas[{{ $i }}][hotel_address]" value="{{ $a['hotel_address'] ?? '' }}" data-place-address>
                  <input type="hidden" name="areas[{{ $i }}][hotel_lat]" value="{{ $a['hotel_lat'] ?? '' }}" data-place-lat>
                  <input type="hidden" name="areas[{{ $i }}][hotel_lon]" value="{{ $a['hotel_lon'] ?? '' }}" data-place-lon>
                </div>
              </details>
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
        <label class="f" for="f-budget-per-person">Target budget per person ({{ old('currency','USD') }})</label>
        <input id="f-budget-per-person" class="in" type="number" name="budget_per_person" value="{{ old('budget_per_person') }}" min="0" placeholder="optional">
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
      <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" style="font-size:13.5px;color:var(--text-dim);">Cancel</a>
    </div>
    @guest
      <p class="hint" style="margin-top:10px;">Next step: a free account to save your trip, so you can open it and invite your group. It takes 20 seconds, and no card is needed.</p>
    @endguest
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
      '<div class="area-main">' +
        '<div class="ps" data-place data-kind="area">' +
          '<input class="in" name="areas[' + i + '][name]" placeholder="Another area…" autocomplete="off" data-place-input>' +
          '<div class="ps-menu" data-place-menu></div>' +
          '<input type="hidden" name="areas[' + i + '][lat]" data-place-lat>' +
          '<input type="hidden" name="areas[' + i + '][lon]" data-place-lon>' +
        '</div>' +
        '<div class="area-distance" data-area-distance style="display:none;"></div>' +
        '<details class="area-hotel">' +
          '<summary>+ Staying somewhere different for this area? (multi-city)</summary>' +
          '<div class="ps" data-place style="margin-top:8px;">' +
            '<input class="in" name="areas[' + i + '][hotel_name]" placeholder="Search a hotel for this area" autocomplete="off" data-place-input>' +
            '<div class="ps-menu" data-place-menu></div>' +
            '<input type="hidden" name="areas[' + i + '][hotel_address]" data-place-address>' +
            '<input type="hidden" name="areas[' + i + '][hotel_lat]" data-place-lat>' +
            '<input type="hidden" name="areas[' + i + '][hotel_lon]" data-place-lon>' +
          '</div>' +
        '</details>' +
      '</div>' +
      '<button type="button" class="icon-btn" data-remove-area>&times;</button>';
    list.appendChild(row);
    row.querySelectorAll('[data-place]').forEach(attachPlace);
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
      var near = wrap.dataset.kind === 'destination' ? '' : nearParam();
      fetch('/places/search?q=' + encodeURIComponent(q) + '&kind=' + (wrap.dataset.kind || '') + near, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
        .then(function (d) {
          menu.innerHTML = '';
          (d.results || []).slice(0, 6).forEach(function (p) {
            var b = document.createElement('button');
            b.type = 'button';
            // Place names come from third parties (OSM is user-edited) — never innerHTML them.
            var strong = document.createElement('strong'); strong.textContent = p.name; b.appendChild(strong);
            if (p.category) { var cat = document.createElement('span'); cat.className = 'addr'; cat.textContent = p.category; cat.style.cssText = 'display:inline; margin-left:6px; color:var(--lavender)'; b.appendChild(cat); }
            if (p.formatted_address) { var ad = document.createElement('span'); ad.className = 'addr'; ad.textContent = p.formatted_address; b.appendChild(ad); }
            b.addEventListener('click', function () {
              input.value = wrap.dataset.kind === 'destination' ? destinationLabel(p) : p.name;
              if (latF) latF.value = p.lat || '';
              if (lonF) lonF.value = p.lon || '';
              if (addrF) addrF.value = p.formatted_address || '';
              menu.classList.remove('on');
              updateDistances();
              wrap.dispatchEvent(new CustomEvent('placepick', { detail: p }));
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

  // Searches lean towards the hotel once it's picked, else the destination.
  function nearParam() {
    var pairs = [['hotel_lat', 'hotel_lon'], ['dest_lat', 'dest_lon']];
    for (var k = 0; k < pairs.length; k++) {
      var la = document.querySelector('[name="' + pairs[k][0] + '"]'), lo = document.querySelector('[name="' + pairs[k][1] + '"]');
      if (la && la.value && lo && lo.value) return '&lat=' + encodeURIComponent(la.value) + '&lon=' + encodeURIComponent(lo.value);
    }
    return '';
  }
  // "Sapporo, Japan" — the country helps the visa and template hints.
  function destinationLabel(p) {
    var parts = (p.formatted_address || '').split(',').map(function (x) { return x.trim(); }).filter(Boolean);
    var country = parts.length ? parts[parts.length - 1] : '';
    return country && country !== p.name ? p.name + ', ' + country : p.name;
  }

  document.querySelectorAll('[data-place]').forEach(attachPlace);

  // ---- approximate distance from hotel per area ----
  function haversineKm(lat1, lon1, lat2, lon2) {
    var toRad = function (d) { return d * Math.PI / 180; };
    var R = 6371;
    var dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1);
    var a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }
  function updateDistances() {
    var mainLatEl = document.querySelector('[name="hotel_lat"]'), mainLonEl = document.querySelector('[name="hotel_lon"]');
    var mainLat = mainLatEl && mainLatEl.value ? parseFloat(mainLatEl.value) : NaN;
    var mainLon = mainLonEl && mainLonEl.value ? parseFloat(mainLonEl.value) : NaN;

    document.querySelectorAll('.area-row').forEach(function (row) {
      var out = row.querySelector('[data-area-distance]');
      var areaLatEl = row.querySelector('[name$="[lat]"]'), areaLonEl = row.querySelector('[name$="[lon]"]');
      var ownHotelLatEl = row.querySelector('[name$="[hotel_lat]"]'), ownHotelLonEl = row.querySelector('[name$="[hotel_lon]"]');
      if (!out || !areaLatEl || !areaLonEl) return;

      var areaLat = parseFloat(areaLatEl.value), areaLon = parseFloat(areaLonEl.value);
      var usingOwnHotel = ownHotelLatEl && ownHotelLatEl.value;
      var hLat = usingOwnHotel ? parseFloat(ownHotelLatEl.value) : mainLat;
      var hLon = usingOwnHotel ? parseFloat(ownHotelLonEl.value) : mainLon;

      if (isNaN(areaLat) || isNaN(areaLon) || isNaN(hLat) || isNaN(hLon)) { out.style.display = 'none'; return; }

      var km = haversineKm(areaLat, areaLon, hLat, hLon);
      var dist = km < 1 ? Math.round(km * 1000) + ' m' : km.toFixed(1) + ' km';
      out.textContent = '📍 ≈ ' + dist + ' from ' + (usingOwnHotel ? "this area's hotel" : 'your hotel');
      out.style.display = 'block';
    });
  }
  updateDistances();

  // ---- starting suggestions once the destination is picked ----
  var startPanel = document.getElementById('startPanel');
  function el(tag, text, cls) { var e = document.createElement(tag); if (text) e.textContent = text; if (cls) e.className = cls; return e; }
  function setPlace(wrap, p, address) {
    wrap.querySelector('[data-place-input]').value = p.name;
    var la = wrap.querySelector('[data-place-lat]'), lo = wrap.querySelector('[data-place-lon]'), ad = wrap.querySelector('[data-place-address]');
    if (la) la.value = p.lat; if (lo) lo.value = p.lon; if (ad) ad.value = address || '';
    updateDistances();
  }
  function emptyAreaWrap() {
    var wraps = list.querySelectorAll('.area-row .area-main > [data-place]');
    for (var k = 0; k < wraps.length; k++) if (!wraps[k].querySelector('[data-place-input]').value.trim()) return wraps[k];
    document.getElementById('addArea').click();
    return list.querySelector('.area-row:last-child .area-main > [data-place]');
  }
  function areaAlreadyListed(name) {
    return Array.prototype.some.call(list.querySelectorAll('.area-row .area-main > [data-place] [data-place-input]'),
      function (i) { return i.value.trim().toLowerCase() === name.toLowerCase(); });
  }
  function loadStart(lat, lon, name) {
    if (!placesEnabled || !startPanel) return;
    startPanel.hidden = false;
    document.getElementById('startHead').textContent = 'Suggested for ' + name;
    var status = document.getElementById('startStatus');
    status.textContent = 'Finding airports, areas and places to stay…';
    ['startAirport', 'startAirportsWrap', 'startAreasWrap', 'startHotelsWrap'].forEach(function (id) { document.getElementById(id).hidden = true; });

    fetch('/places/explore?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon), { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d) { status.textContent = 'Suggestions are unavailable right now — search for your hotel and areas below.'; return; }

        // Airports: tap one to use it for the arrival + return flights (the nearest isn't always the one you fly into).
        var aps = d.airports || [], apBox = document.getElementById('startAirports');
        apBox.innerHTML = '';
        aps.forEach(function (ap) {
          var b = el('button'); b.type = 'button';
          b.appendChild(document.createTextNode(ap.name)); b.appendChild(el('small', ap.km + ' km'));
          b.addEventListener('click', function () {
            var to = document.querySelector('[name="segments[0][to]"]'), from = document.querySelector('[name="segments[1][from]"]');
            if (to) to.value = ap.provider_id; if (from) from.value = ap.provider_id;
            document.getElementById('startAirport').textContent = '✈️ ' + ap.provider_id + ' set as your arrival and return airport.';
            document.getElementById('startAirport').hidden = false;
          });
          apBox.appendChild(b);
        });
        document.getElementById('startAirportsWrap').hidden = !aps.length;

        // Areas where the hotels cluster; the busiest one starts area 1 if it's empty.
        var areas = d.areas || [], areaBox = document.getElementById('startAreas');
        areaBox.innerHTML = '';
        areas.forEach(function (a, n) {
          var b = el('button'); b.type = 'button';
          b.appendChild(document.createTextNode(a.name)); b.appendChild(el('small', a.hotels + ' hotels · ' + a.km + ' km'));
          b.addEventListener('click', function () { if (!areaAlreadyListed(a.name)) setPlace(emptyAreaWrap(), a); });
          areaBox.appendChild(b);
        });
        document.getElementById('startAreasWrap').hidden = !areas.length;
        var first = list.querySelector('.area-row .area-main > [data-place]');
        var autoArea = areas[0] && first && !first.querySelector('[data-place-input]').value.trim();
        if (autoArea) setPlace(first, areas[0]);

        var hotels = d.hotels || [], hotelBox = document.getElementById('startHotels');
        var hotelWrap = document.querySelector('[data-place][data-kind="hotel"]');
        hotelBox.innerHTML = '';
        hotels.slice(0, 8).forEach(function (h) {
          var b = el('button'); b.type = 'button';
          b.appendChild(document.createTextNode(h.name)); b.appendChild(el('small', (h.category ? h.category + ' · ' : '') + h.km + ' km'));
          b.addEventListener('click', function () { setPlace(hotelWrap, h, h.formatted_address); });
          hotelBox.appendChild(b);
        });
        document.getElementById('startHotelsWrap').hidden = !hotels.length;

        status.textContent = (autoArea ? 'Area 1 is set to ' + areas[0].name + ' — change it anytime. ' : '') +
          (d.partial ? 'Some suggestions didn\'t load — try picking the destination again in a minute.' : 'From OpenStreetMap and OurAirports.');
      })
      .catch(function () { status.textContent = 'Suggestions are unavailable right now — search for your hotel and areas below.'; });
  }
  var destWrap = document.getElementById('destWrap');
  if (destWrap) {
    destWrap.addEventListener('placepick', function (e) {
      checkVisa(); checkTemplate();
      loadStart(e.detail.lat, e.detail.lon, destWrap.querySelector('[data-place-input]').value);
    });
  }

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

  // ---- recommended-template hint ----
  var TPLS = @json($templates ?? []);
  var tplHint = document.getElementById('templateHint');
  function checkTemplate() {
    var q = destInput.value.trim().toLowerCase();
    if (!q) { tplHint.style.display = 'none'; return; }
    var hit = TPLS.find(function (t) { return q.indexOf(t.destination.toLowerCase()) !== -1; });
    if (!hit) { tplHint.style.display = 'none'; return; }
    tplHint.innerHTML = '💡 <strong>Recommended: ' + hit.trip_length + '</strong>, best ' + hit.best_season + '. ' + hit.overview;
    tplHint.style.display = 'block';
  }
  if (destInput) { destInput.addEventListener('input', checkTemplate); checkTemplate(); }

  // Coming back to the form (validation error) with a picked destination: show its suggestions again.
  var dLat = document.querySelector('[name="dest_lat"]'), dLon = document.querySelector('[name="dest_lon"]');
  if (dLat && dLat.value && dLon && dLon.value) loadStart(dLat.value, dLon.value, destInput.value);
})();
</script>
@endsection
