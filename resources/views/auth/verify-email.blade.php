@extends('layouts.site')

@section('title', 'Verify your email · Travel2gether')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1 class="gtext">Verify your email</h1>
    <p class="lede">
      Thanks for signing up. Click the link in the email we just sent to
      <strong>{{ auth()->user()->email }}</strong> before you start planning.
    </p>

    @if (session('status') === 'verification-link-sent')
      <div class="form-error" style="background:rgba(59,167,118,0.1);border-color:#8fd3b4;color:#2f6d54;">
        A fresh verification link is on its way.
      </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn btn-primary btn-block">Resend verification email</button>
    </form>

    <p class="card-alt">
      <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
        <button type="submit" style="background:none;border:none;color:var(--text-dim);font:inherit;cursor:pointer;padding:0;">Log out</button>
      </form>
    </p>
  </div>
</div>
@endsection
