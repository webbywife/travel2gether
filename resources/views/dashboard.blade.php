@extends('layouts.site')

@section('title', 'My trips · Travel2gether')

@push('styles')
<style>
  .dash{max-width:900px; margin:44px auto 0; padding:0 24px;}
  .dash h1{font-family:'Space Grotesk',sans-serif; font-size:30px; margin:0 0 6px; letter-spacing:-0.015em;}
  .dash .lede{color:var(--text-dim); margin:0 0 28px;}
  .dash h2{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin:34px 0 14px;}
  .flash{background:rgba(59,167,118,0.1); border:1px solid #8fd3b4; color:#2f6d54; border-radius:12px; padding:12px 16px; font-size:14px; margin-bottom:20px;}

  .trip-grid{display:grid; grid-template-columns:repeat(2,1fr); gap:16px;}
  .trip-card{
    display:block; text-decoration:none; color:inherit;
    border:1px solid var(--line); border-radius:16px; padding:20px; background:rgba(255,255,255,0.92);
    box-shadow:var(--shadow-sm); transition:transform .16s ease, box-shadow .16s ease;
  }
  .trip-card:hover{transform:translateY(-3px); box-shadow:var(--shadow-md);}
  .trip-card .dest{font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:var(--pink);}
  .trip-card .name{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:18px; margin:6px 0 4px; letter-spacing:-0.01em;}
  .trip-card .meta{font-size:13px; color:var(--text-dim);}
  .role-badge{display:inline-block; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.06em; text-transform:uppercase; border:1px solid var(--line); border-radius:999px; padding:2px 8px; color:var(--text-dim); margin-left:6px;}

  .empty{border:1px dashed var(--pink-light); border-radius:16px; padding:36px 28px; text-align:center; background:var(--panel);}
  .empty h3{font-family:'Space Grotesk',sans-serif; font-size:19px; margin:0 0 8px;}
  .empty p{color:var(--text-dim); max-width:46ch; margin:0 auto 18px;}

  @media (max-width:680px){ .trip-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="dash">
  <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>
      <h1>Hi, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}</h1>
      <p class="lede">Your trips and the ones you've been invited to.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('trips.create') }}">Plan a new trip</a>
  </div>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  @if ($owned->isEmpty() && $shared->isEmpty())
    <div class="empty">
      <h3>No trips yet</h3>
      <p>Plan your own from flights, dates, hotel and the areas you want — or start from a sample and edit it.</p>
      <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
        <a class="btn btn-primary" href="{{ route('trips.create') }}">Plan a new trip</a>
        @foreach ($samples as $s)
          <form method="POST" action="{{ route('trips.duplicate', $s) }}">
            @csrf
            <button type="submit" class="btn btn-ghost">Start from {{ $s->title }}</button>
          </form>
        @endforeach
      </div>
    </div>
  @else
    @if ($owned->isNotEmpty())
      <h2>Trips you own</h2>
      <div class="trip-grid">
        @foreach ($owned as $trip)
          <a class="trip-card" href="{{ route('trips.show', $trip) }}">
            <span class="dest">{{ $trip->destination }}</span>
            <div class="name">{{ $trip->title }}</div>
            <div class="meta">
              {{ $trip->start_date->format('M j') }}–{{ $trip->end_date->format('M j, Y') }}
              · {{ $trip->members_count }} {{ Str::plural('collaborator', $trip->members_count) }}
              @unless ($trip->is_public)<span class="role-badge">private</span>@endunless
            </div>
          </a>
        @endforeach
      </div>
    @endif

    @if ($shared->isNotEmpty())
      <h2>Shared with you</h2>
      <div class="trip-grid">
        @foreach ($shared as $trip)
          <a class="trip-card" href="{{ route('trips.show', $trip) }}">
            <span class="dest">{{ $trip->destination }}</span>
            <div class="name">{{ $trip->title }}<span class="role-badge">{{ $trip->pivot->role }}</span></div>
            <div class="meta">
              {{ $trip->start_date->format('M j') }}–{{ $trip->end_date->format('M j, Y') }}
              · {{ $trip->members_count }} {{ Str::plural('collaborator', $trip->members_count) }}
            </div>
          </a>
        @endforeach
      </div>
    @endif

    @if ($sampleTrip)
      <p style="margin-top:26px;font-size:13.5px;color:var(--text-dim);">
        Want to try another? <a href="{{ route('trips.show', $sampleTrip) }}">Open the sample</a> and hit “Make a copy”.
      </p>
    @endif
  @endif
</div>
@endsection
