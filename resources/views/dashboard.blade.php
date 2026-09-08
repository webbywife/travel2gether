@extends('layouts.site')

@section('title', 'My trips · Travel2gether')

@push('styles')
<style>
  .dash{max-width:820px; margin:44px auto 0; padding:0 24px;}
  .dash h1{font-family:'Space Grotesk',sans-serif; font-size:30px; margin:0 0 6px;}
  .dash .lede{color:var(--text-dim); margin:0 0 28px;}
  .empty{border:1px dashed var(--pink-light); border-radius:16px; padding:36px 28px; text-align:center; background:var(--panel);}
  .empty h2{font-family:'Space Grotesk',sans-serif; font-size:20px; margin:0 0 8px;}
  .empty p{color:var(--text-dim); max-width:46ch; margin:0 auto 20px;}
  .roadmap{margin-top:34px; border-top:1px solid var(--line); padding-top:22px;}
  .roadmap h3{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin:0 0 12px;}
  .roadmap ul{margin:0; padding-left:18px; color:var(--text-dim); font-size:14.5px;}
  .roadmap li{margin-bottom:7px;}
</style>
@endpush

@section('content')
<div class="dash">
  <h1>Hi, {{ Str::of(auth()->user()->name)->before(' ') }}</h1>
  <p class="lede">This is where your trips will live.</p>

  <div class="empty">
    <h2>No trips yet</h2>
    <p>AI trip generation is the next thing we're building. For now, take a full itinerary for a spin:</p>
    @if($sampleTrip ?? null)
      <a class="btn btn-primary" href="{{ route('trips.show', $sampleTrip) }}">Open the sample itinerary</a>
    @endif
  </div>

  <div class="roadmap">
    <h3>Coming next</h3>
    <ul>
      <li>Describe a trip and get a full AI-generated draft — your first one free</li>
      <li>Invite your group with a link · owner / editor / viewer roles</li>
      <li>Picks that sync live across everyone's devices, with attribution</li>
      <li>Weather-driven swaps with the cost difference rolled into the budget</li>
    </ul>
  </div>
</div>
@endsection
