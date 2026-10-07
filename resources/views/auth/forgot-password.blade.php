@extends('layouts.site')

@section('title', 'Forgot password · Travel2gether')
@section('robots', 'noindex')

@section('content')
<div class="card-wrap">
  <div class="card">
    <h1>Forgot your password?</h1>
    <p class="lede">Enter the email you signed up with and we'll send you a link to choose a new one.</p>

    @if (session('status'))
      <div class="form-ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="form-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Email me a reset link</button>
    </form>

    <p class="card-alt">Signed up with Google? Just use <b>Continue with Google</b> on the <a href="{{ route('login') }}">login page</a> — there's no password to reset.</p>
    <p class="card-alt" style="margin-top:6px;"><a href="{{ route('login') }}">← Back to log in</a></p>
  </div>
</div>
@endsection
