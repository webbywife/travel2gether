@extends('layouts.site')

@section('title', 'Your profile · Travel2gether')

@push('styles')
<style>
  .prof{max-width:640px; margin:44px auto 60px; padding:0 24px;}
  .prof h1{font-family:'Space Grotesk',sans-serif; font-size:28px; margin:0 0 6px;}
  .prof .lede{color:var(--text-dim); margin:0 0 26px;}
  .prof h2{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin:32px 0 14px;}
  .card{border:1px solid var(--line); border-radius:16px; background:rgba(255,255,255,0.94); box-shadow:var(--shadow-sm); padding:22px;}
  .flash{background:rgba(59,167,118,0.1); border:1px solid #8fd3b4; color:#2f6d54; border-radius:12px; padding:12px 16px; font-size:14px; margin-bottom:20px;}
  .form-error{background:rgba(225,74,128,0.08); border:1px solid var(--pink-light); color:var(--accent); border-radius:10px; padding:12px 14px; font-size:13.5px; margin-bottom:18px;}
  .form-error ul{margin:0; padding-left:18px;}
  label.f{display:block; font-size:12.5px; color:var(--text-dim); margin-bottom:4px;}
  .in{width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:var(--panel); font:inherit; font-size:14.5px; color:var(--text); margin-bottom:14px;}
  .in:focus{outline:2px solid var(--pink-light); border-color:var(--pink);}
  .avatar-row{display:flex; align-items:center; gap:14px; margin-bottom:18px;}
  .avatar-row img{width:56px; height:56px; border-radius:50%; object-fit:cover;}
  .avatar-fallback{width:56px; height:56px; border-radius:50%; background:var(--grad-soft); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:20px;}
  .google-tag{font-family:'JetBrains Mono',monospace; font-size:11px; color:var(--text-dim); background:var(--panel); border-radius:999px; padding:4px 10px; display:inline-block; margin-top:2px;}
  .trip-mini{display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--line); font-size:14px;}
  .trip-mini:last-child{border-bottom:none;}
  .trip-mini a{font-size:12.5px;}
</style>
@endpush

@section('content')
<div class="prof">
  <h1><span class="gtext">Your profile</span></h1>
  <p class="lede">Name, email, and password. Your trips are managed from each trip's own page.</p>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="form-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <div class="card">
    <div class="avatar-row">
      @if($user->avatar)
        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
      @else
        <div class="avatar-fallback">{{ \Illuminate\Support\Str::of($user->name)->trim()->substr(0,1)->upper() }}</div>
      @endif
      <div>
        <div style="font-weight:600;">{{ $user->name }}</div>
        @if($user->google_id)<span class="google-tag">🔗 linked to Google</span>@endif
      </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}">
      @csrf
      @method('PATCH')
      <label class="f">Name</label>
      <input class="in" name="name" value="{{ old('name', $user->name) }}" required>
      <label class="f">Email</label>
      <input class="in" type="email" name="email" value="{{ old('email', $user->email) }}" required>
      @unless($user->email_verified_at)
        <p style="font-size:12.5px; color:var(--text-dim); margin:-8px 0 14px;">Not yet verified.</p>
      @endunless
      <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
  </div>

  <h2>{{ $user->password ? 'Change password' : 'Set a password' }}</h2>
  <div class="card">
    @unless($user->password)
      <p style="font-size:13.5px; color:var(--text-dim); margin:0 0 14px;">You signed up with Google, so there's no password yet — set one if you'd also like to log in directly.</p>
    @endunless
    <form method="POST" action="{{ route('profile.password') }}">
      @csrf
      @method('PUT')
      @if($user->password)
        <label class="f">Current password</label>
        <input class="in" type="password" name="current_password" required>
      @endif
      <label class="f">New password</label>
      <input class="in" type="password" name="password" required minlength="12">
      <label class="f">Confirm new password</label>
      <input class="in" type="password" name="password_confirmation" required minlength="12">
      <button type="submit" class="btn btn-primary">{{ $user->password ? 'Change password' : 'Set password' }}</button>
    </form>
  </div>

  @if ($owned->isNotEmpty() || $shared->isNotEmpty())
    <h2>Your trips</h2>
    <div class="card">
      @foreach ($owned as $trip)
        <div class="trip-mini">
          <span>{{ $trip->title }} <span style="color:var(--text-dim);">· {{ $trip->destination }}</span></span>
          <a href="{{ route('trips.edit', $trip) }}">Edit →</a>
        </div>
      @endforeach
      @foreach ($shared as $trip)
        <div class="trip-mini">
          <span>{{ $trip->title }} <span style="color:var(--text-dim);">· shared with you</span></span>
          <a href="{{ route('trips.show', $trip) }}">Open →</a>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
