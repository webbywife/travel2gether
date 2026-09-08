@extends('layouts.site')

@section('title', 'Privacy & cookies · Travel2gether')
@section('meta_description', 'What Travel2gether collects, the cookies and local storage it uses, and which third parties (Google Sign-In, Places, Gemini AI, Open-Meteo) your data passes through.')

@push('styles')
<style>
  .legal{max-width:760px; margin:44px auto 80px; padding:0 24px;}
  .legal h1{font-family:'Space Grotesk',sans-serif; font-size:30px; margin:0 0 6px; letter-spacing:-0.015em;}
  .legal .updated{color:var(--text-dim); font-size:13px; margin:0 0 28px;}
  .legal section{background:rgba(255,255,255,0.9); border:1px solid var(--line); border-radius:16px; padding:22px 24px; margin-bottom:16px; box-shadow:var(--shadow-sm);}
  .legal h2{font-family:'Space Grotesk',sans-serif; font-size:17px; margin:0 0 10px; letter-spacing:-0.01em;}
  .legal p{margin:0 0 10px; color:var(--text); font-size:14.5px; line-height:1.65;}
  .legal p:last-child{margin-bottom:0;}
  .legal ul{margin:0 0 10px; padding-left:20px; font-size:14.5px; line-height:1.65;}
  .legal li{margin-bottom:6px;}
  .legal table{width:100%; border-collapse:collapse; font-size:13.5px; margin:4px 0 10px;}
  .legal th, .legal td{text-align:left; padding:8px 10px; border-bottom:1px solid var(--line); vertical-align:top;}
  .legal th{font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.04em; text-transform:uppercase; color:var(--text-dim);}
  .legal .note{background:var(--panel); border-left:3px solid var(--pink); border-radius:0 10px 10px 0; padding:10px 14px; font-size:13px; color:var(--text-dim);}
</style>
@endpush

@section('content')
<div class="legal">
  <h1><span class="gtext">Privacy &amp; cookies</span></h1>
  <p class="updated">Last updated {{ now()->format('F Y') }}. Plain-language summary — not a substitute for legal advice.</p>

  <section>
    <h2>What we collect</h2>
    <p>If you just browse the landing page or a shared/sample trip, we don't ask for anything. Creating an account or a trip collects:</p>
    <ul>
      <li><strong>Account:</strong> name, email, and a password (stored hashed) — or, if you sign in with Google, your name, email, profile photo and a Google account ID instead of a password.</li>
      <li><strong>Trip content:</strong> destinations, dates, flight details, hotel name, areas, interests, budget figures, and anything you or your collaborators type into a trip.</li>
      <li><strong>Collaboration:</strong> who's a member of a trip and their role, invite links you create, and which option a member picks (attributed to that member, visible to the rest of the trip).</li>
      <li><strong>Trippie chat:</strong> messages you send the assistant, to generate a reply. These are not saved on our server after the request completes.</li>
    </ul>
  </section>

  <section>
    <h2>Cookies &amp; browser storage</h2>
    <p>We don't run ads or ad-tracking scripts, so there's no ad-tech cookie wall here. What we do use:</p>
    <table>
      <tr><th>What</th><th>Purpose</th><th>Where</th></tr>
      <tr><td>Session cookie</td><td>Keeps you signed in</td><td>Cookie, expires when you log out or after inactivity</td></tr>
      <tr><td>CSRF token cookie</td><td>Blocks cross-site request forgery</td><td>Cookie, session-length</td></tr>
      <tr><td>Trip picks</td><td>Remembers which option you tapped on a trip</td><td>Your browser's local storage only — never sent to us</td></tr>
      <tr><td>Trippie history</td><td>Keeps the chat visible while you browse</td><td>Your browser's session storage only, cleared when the tab closes</td></tr>
    </table>
    <p>Because the picks and chat history live in your browser's storage, not ours, clearing your browser data or using a different device/browser resets them.</p>
  </section>

  <section>
    <h2>Third parties we send data to</h2>
    <p>To provide specific features, some data leaves Travel2gether and goes to:</p>
    <ul>
      <li><strong>Google Sign-In</strong> — if you use it, to authenticate you.</li>
      <li><strong>Google Places API</strong> — search text and an approximate location, to look up real places for the trip builder and AI drafting.</li>
      <li><strong>Google Gemini (AI)</strong> — your trip's destination, dates, areas, interests, and hotel name (to draft a day), and whatever you type to Trippie (plus your current trip's summary, if you're on a trip page). <strong>Don't paste anything sensitive into a trip field or the chat</strong> — treat both like you would a message to any third-party AI tool.</li>
      <li><strong>Open-Meteo</strong> — a location's coordinates and a date, to fetch weather. No personal or account data is sent.</li>
    </ul>
    <p class="note">None of these providers are paid to advertise back to you based on this data, and we don't sell or rent your information to anyone.</p>
  </section>

  <section>
    <h2>Who can see what</h2>
    <p>A private trip is visible only to its owner and the collaborators invited to it. A public trip (like the seeded samples) is visible to anyone with the link. Presence — "who's viewing this trip right now" — is only shown to other members of that same trip, never to strangers.</p>
  </section>

  <section>
    <h2>How long we keep it, and your choices</h2>
    <p>We keep account and trip data for as long as your account exists. Security logs (sign-in attempts, etc.) are kept for 90 days. To access, correct, or delete your data — including closing your account —
    @if($contact)
      email <a href="mailto:{{ $contact }}">{{ $contact }}</a>.
    @else
      contact the site owner (a self-service option is coming; for now, reach out through the account you signed up with).
    @endif
    </p>
  </section>

  <section>
    <h2>Children</h2>
    <p>Travel2gether isn't directed at children and isn't intended for use by anyone who can't independently agree to these terms.</p>
  </section>

  <section>
    <h2>Changes</h2>
    <p>If this policy changes in a way that matters, we'll update the date at the top of this page.</p>
  </section>
</div>
@endsection
