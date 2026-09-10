@extends('layouts.site')

@section('title', 'Edit ' . $trip->title . ' · Travel2gether')

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
  .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 14px; font-size:13.5px; margin-bottom:18px;}
  .form-error ul{margin:0; padding-left:18px;}
  .actions{display:flex; gap:12px; align-items:center; margin:6px 0 60px;}
  @media (max-width:620px){ .g2{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="build">
  <h1><span class="gtext">Edit trip details</span></h1>
  <p class="lede">Title, destination, hotel and party size — dates, flights, and day-by-day areas stay put (duplicate the trip if you need to change those).</p>

  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('trips.update', $trip) }}">
    @csrf
    @method('PATCH')

    <div class="step">
      <div class="grid g2">
        <div>
          <label class="f">Destination *</label>
          <input class="in" id="destinationInput" name="destination" value="{{ old('destination', $trip->destination) }}" placeholder="e.g. San Francisco, USA" required>
          <div id="visaHint" style="display:none; margin-top:7px; font-size:12px;"></div>
          <div id="templateHint" style="display:none; margin-top:7px; font-size:12px; color:var(--text-dim); background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:8px 11px;"></div>
        </div>
        <div><label class="f">Trip name</label><input class="in" name="title" value="{{ old('title', $trip->title) }}" placeholder="optional"></div>
        <div><label class="f">Travellers</label><input class="in" type="number" name="party_size" value="{{ old('party_size', $trip->party_size) }}" min="1" max="20"></div>
        <div><label class="f">Currency</label>
          <select class="in" name="currency">
            @foreach (['USD','PHP','EUR','JPY','KRW','GBP','SGD','AUD'] as $c)
              <option value="{{ $c }}" @selected(old('currency', $trip->currency) === $c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div><label class="f">Hotel name</label><input class="in" name="hotel_name" value="{{ old('hotel_name', $trip->hotel_name) }}"></div>
        <div><label class="f">Hotel address</label><input class="in" name="hotel_address" value="{{ old('hotel_address', $trip->hotel_address) }}"></div>
      </div>
    </div>

    <div class="actions">
      <button type="submit" class="btn btn-primary">Save changes</button>
      <a href="{{ route('trips.show', $trip) }}" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<script>
(function () {
  // ---- visa hint (same matcher as the "new trip" wizard) ----
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
})();
</script>
@endsection
