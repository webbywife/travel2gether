@extends('layouts.site')

@section('title', 'Create your account · Travel2gether')
@section('meta_description', 'Sign up for Travel2gether — your first full AI-generated itinerary is free, no card required.')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1>Start planning</h1>
    <p class="lede">Your first full AI itinerary is free — no card required.</p>

    @if ($errors->any())
      <div class="form-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @include('auth._google')

    <form method="POST" action="{{ route('register') }}">
      @csrf
      <div class="field">
        <label for="name">Your name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="new-password">
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Create my free account</button>
    </form>

    <p class="card-alt">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
  </div>
</div>
@endsection
