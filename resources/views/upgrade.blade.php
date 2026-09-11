@extends('layouts.site')

@section('title', 'Upgrade · Travel2gether')

@push('styles')
<style>
  .up{max-width:560px; margin:60px auto; padding:0 24px; text-align:center;}
  .up h1{font-family:'Space Grotesk',sans-serif; font-size:30px; margin:0 0 14px;}
  .up p{color:var(--text-dim); line-height:1.65; margin:0 0 22px;}
  .up .card{border:1px solid var(--line); border-radius:18px; background:rgba(255,255,255,0.94); box-shadow:var(--shadow-sm); padding:28px; text-align:left;}
  .up .card h3{font-family:'Space Grotesk',sans-serif; margin:0 0 8px; font-size:18px;}
  .up ul{color:var(--text-dim); font-size:14px; line-height:1.8; padding-left:20px; margin:0 0 20px;}
  .up .flash{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 16px; font-size:14px; margin:0 0 22px; text-align:left;}
</style>
@endpush

@section('content')
<div class="up">
  <h1><span class="gtext">Upgrade — coming soon</span></h1>
  <p>Free membership caps trips and AI re-drafts. Paid subscriptions aren't live yet — this page is a placeholder for when they are.</p>

  @if (session('error'))<div class="flash">{{ session('error') }}</div>@endif

  <div class="card">
    <h3>What's planned</h3>
    <ul>
      <li>Free members: up to {{ \App\Models\User::FREE_TRIP_LIMIT }} trips at a time, and one AI re-draft per trip.</li>
      <li>Paid members: unlimited trips, and unlimited AI re-drafts on trips they own.</li>
      <li>No subscription is charged automatically today — this is a placeholder, not a checkout.</li>
      <li>Your first AI draft of every day always stays free either way.</li>
    </ul>
    <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to your trips</a>
  </div>
</div>
@endsection
