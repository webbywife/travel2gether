{{-- The one site footer: used by layouts.site and the trip pages. Styles live in public/css/t2g.css. --}}
<footer class="site-foot">
  <div class="foot-in">
    <div class="foot-top">
      <div>
        <p class="foot-pitch">Plan the trip together — then actually go.</p>
        <a class="btn btn-primary" href="{{ route('trips.start') }}" style="color:#fff;">Start planning <span class="arr" aria-hidden="true"></span></a>
      </div>
      <div>
        <h4>Plan</h4>
        <a href="{{ route('trips.start') }}">New trip</a>
        <a href="{{ route('samples') }}">Sample trips</a>
        <a href="{{ route('home') }}#how">How it works</a>
      </div>
      <div>
        <h4>Explore</h4>
        <a href="{{ route('destinations') }}">Destinations</a>
        @if(\App\Support\Gallery::places())<a href="{{ route('gallery') }}">Gallery</a>@endif
        <a href="https://www.instagram.com/traveleyz" rel="noopener" target="_blank">Instagram</a>
      </div>
      <div>
        <h4>Account</h4>
        @auth
          <a href="{{ route('dashboard') }}">My trips</a>
          <a href="{{ route('profile.edit') }}">Profile</a>
        @else
          <a href="{{ route('login') }}">Log in</a>
          <a href="{{ route('register') }}">Create an account</a>
        @endauth
        <a href="{{ route('privacy') }}">Privacy &amp; cookies</a>
      </div>
    </div>
    <span class="foot-word" aria-hidden="true">travel<span>2</span>gether</span>
    <div class="foot-base">
      <span>© {{ date('Y') }} Travel2gether · Plan · Together</span>
      <button type="button" class="motion-toggle" data-motion-toggle aria-pressed="false">Motion: on</button>
    </div>
  </div>
</footer>
