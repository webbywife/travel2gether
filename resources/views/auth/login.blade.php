@extends('layouts.site')

@section('title', 'Log in · Travel2gether')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1>Welcome back</h1>
    <p class="lede">Log in to pick up where your group left off.</p>

    @if ($errors->any())
      <div class="form-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <div class="form-row">
        <label style="display:flex; align-items:center; gap:7px; margin:0;">
          <input type="checkbox" name="remember" style="width:auto;"> Stay logged in
        </label>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <p class="card-alt">New here? <a href="{{ route('register') }}">Create a free account</a></p>
  </div>
</div>
@endsection
