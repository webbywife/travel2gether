@extends('layouts.site')

@section('title', 'Check your email · Travel2gether')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1 class="gtext">Almost there</h1>
    <p class="lede">
      We've sent a verification link to
      <strong>{{ session('pending_email', 'your email address') }}</strong>.
      Click it to finish setting up your account.
    </p>
    <p style="font-size:13.5px;color:var(--text-dim);margin:0 0 4px;">
      Didn't get it? Check spam, or
      @auth
        <form method="POST" action="{{ route('verification.send') }}" style="display:inline">@csrf
          <button type="submit" style="background:none;border:none;color:var(--pink);font:inherit;cursor:pointer;padding:0;text-decoration:underline;">send it again</button>.
        </form>
      @else
        <a href="{{ route('login') }}">log in</a> to resend it.
      @endauth
    </p>
    <p class="card-alt">Already verified? <a href="{{ route('login') }}">Log in</a></p>
  </div>
</div>
@endsection
