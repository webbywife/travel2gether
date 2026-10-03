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
        <div><label class="f" for="f-title">Trip name</label><input id="f-title" class="in" name="title" value="{{ old('title', $trip->title) }}" placeholder="optional"></div>
        <div><label class="f" for="f-party-size">Travellers</label><input id="f-party-size" class="in" type="number" name="party_size" value="{{ old('party_size', $trip->party_size) }}" min="1" max="20"></div>
        <div><label class="f" for="f-currency">Currency</label>
          <select id="f-currency" class="in" name="currency">
            @foreach (['USD','PHP','EUR','JPY','KRW','GBP','SGD','AUD'] as $c)
              <option value="{{ $c }}" @selected(old('currency', $trip->currency) === $c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div><label class="f" for="f-hotel-name">Hotel name</label><input id="f-hotel-name" class="in" name="hotel_name" value="{{ old('hotel_name', $trip->hotel_name) }}"></div>
        <div><label class="f" for="f-hotel-address">Hotel address</label><input id="f-hotel-address" class="in" name="hotel_address" value="{{ old('hotel_address', $trip->hotel_address) }}"></div>
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
@can('delete', $trip)
@unless($trip->isSample())
<div class="build" style="margin-top:-30px;">
  <div class="step" style="border-color:rgba(194,42,102,0.35);">
    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:17px; margin:0 0 6px; color:var(--pink);">Delete this trip</h2>
    <p style="font-size:13.5px; color:var(--text-dim); margin:0 0 12px;">Removes every day, stop, pick and collaborator for good. This can't be undone. Type <b>{{ $trip->title }}</b> to confirm.</p>
    @error('confirm_title')<div class="form-error">{{ $message }}</div>@enderror
    <form method="POST" action="{{ route('trips.destroy', $trip) }}" style="display:flex; gap:10px; flex-wrap:wrap;">
      @csrf
      @method('DELETE')
      <label for="confirm_title" class="sr-only" style="position:absolute; left:-9999px;">Trip name</label>
      <input class="in" id="confirm_title" name="confirm_title" placeholder="{{ $trip->title }}" autocomplete="off" style="flex:1; min-width:200px;">
      <button type="submit" class="btn" style="background:var(--pink); color:#fff;">Delete trip</button>
    </form>
  </div>
</div>
@endunless
@endcan
@endsection
