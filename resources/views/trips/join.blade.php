@extends('layouts.site')

@section('title', 'Join a trip · Travel2gether')

@section('content')
<div class="card-wrap">
  <div class="card">
    @if ($expired ?? false)
      <h1 class="gtext">Link expired</h1>
      <p class="lede">This invite link is no longer valid. Ask the trip owner for a fresh one.</p>
      <a class="btn btn-primary btn-block" href="{{ route('dashboard') }}">Go to my trips</a>
    @elseif ($wrongAccount ?? false)
      <h1 class="gtext">Wrong account</h1>
      <p class="lede">This invite was sent to <strong>{{ $invite->email }}</strong>. Log in with that address to accept it.</p>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="btn btn-primary btn-block">Switch account</button>
      </form>
    @else
      <h1 class="gtext">You're invited</h1>
      <p class="lede">
        <strong>{{ $invite->creator->name ?? 'Someone' }}</strong> invited you to
        <strong>{{ $invite->trip->title }}</strong> as
        <strong>{{ $invite->role }}</strong>.
      </p>
      <form method="POST" action="{{ route('trips.join.accept', $invite->token) }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">Join this trip</button>
      </form>
      <p class="card-alt">
        Joining as {{ auth()->user()->email }} · <a href="{{ route('dashboard') }}">not you?</a>
      </p>
    @endif
  </div>
</div>
@endsection
