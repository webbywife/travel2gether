@extends('layouts.site')

@section('title', $title . ' · Travel2gether')

@section('content')
<div class="card-wrap" style="text-align:center;">
  <div class="card">
    <div style="font-size:44px; line-height:1; margin-bottom:10px;">{{ $emoji }}</div>
    <h1>{{ $title }}</h1>
    <p class="lede">{{ $message }}</p>
    <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
      <a class="btn btn-primary" href="{{ route('home') }}">Go to the home page</a>
      <a class="btn btn-ghost" href="{{ route('destinations') }}">Browse destinations</a>
    </div>
  </div>
</div>
@endsection
