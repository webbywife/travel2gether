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
</style>
@endpush

@section('content')
<div class="up">
  <h1><span class="gtext">More re-drafts, coming soon</span></h1>
  <p>Every trip gets one free AI re-draft. Paid regeneration credits aren't live yet — this page is a placeholder for when they are.</p>
  <div class="card">
    <h3>What's planned</h3>
    <ul>
      <li>Buy a small pack of re-draft credits, spend them on any day, any trip.</li>
      <li>No subscription — pay only when you actually want another AI pass.</li>
      <li>Your first draft of every day always stays free, no matter what.</li>
    </ul>
    <a class="btn btn-primary" href="{{ route('dashboard') }}">Back to your trips</a>
  </div>
</div>
@endsection
