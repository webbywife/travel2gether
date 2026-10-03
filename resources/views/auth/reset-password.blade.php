@extends('layouts.site')

@section('title', 'Choose a new password · Travel2gether')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1>Choose a new password</h1>
    <p class="lede">At least 12 characters. You'll be logged out of any other devices.</p>

    @if ($errors->any())
      <div class="form-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
      </div>
      <div class="field">
        <label for="password">New password</label>
        <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" autofocus>
      </div>
      <div class="field">
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Save new password</button>
    </form>
  </div>
</div>
@endsection
