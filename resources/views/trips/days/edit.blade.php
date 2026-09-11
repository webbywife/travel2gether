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
</style>
@endpush

@section('content')
<div class="build">
  <h1><span class="gtext">Edit Day {{ $day->day_number }}</span></h1>
  <p class="lede">Title, area, hotel, and notes for this day. Stops and their options stay as-is here — edit those by re-drafting the day with AI.</p>

  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
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
@endsection
