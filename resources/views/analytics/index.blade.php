@extends('layouts.site')

@section('title', 'Analytics · Travel2gether')

@push('styles')
<style>
  .an{max-width:1000px; margin:44px auto 60px; padding:0 24px;}
  .an h1{font-family:'Space Grotesk',sans-serif; font-size:28px; margin:0 0 6px;}
  .an .lede{color:var(--text-dim); margin:0 0 28px;}
  .an h2{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-dim); margin:34px 0 14px;}
  .stat-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px;}
  .stat{border:1px solid var(--line); border-radius:14px; padding:16px; background:rgba(255,255,255,0.92); text-align:center;}
  .stat .num{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:26px; color:var(--accent);}
  .stat .lbl{font-size:12px; color:var(--text-dim); margin-top:4px;}
  .an-grid{display:grid; grid-template-columns:1fr 1fr; gap:20px;}
  .an-card{border:1px solid var(--line); border-radius:14px; padding:18px; background:rgba(255,255,255,0.92);}
  .an-list{list-style:none; margin:0; padding:0;}
  .an-list li{display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--line); font-size:14px;}
  .an-list li:last-child{border-bottom:none;}
  .an-list .bar{color:var(--text-dim); font-family:'JetBrains Mono',monospace; font-size:12px;}
  .empty-note{color:var(--text-dim); font-size:13.5px; padding:20px 0; text-align:center;}
  .member-row{display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0; border-bottom:1px solid var(--line); font-size:13.5px;}
  .member-row:last-child{border-bottom:none;}
  .member-row .who{display:flex; flex-direction:column;}
  .member-row .who span{color:var(--text-dim); font-size:12px;}
  .tier-tag{font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.05em; text-transform:uppercase; border-radius:999px; padding:3px 9px;}
  .tier-tag.free{background:var(--panel); color:var(--text-dim);}
  .tier-tag.paid{background:rgba(59,167,118,0.14); color:#2f6d54;}
  .tier-tag.admin{background:rgba(113,86,168,0.14); color:#5a4488;}
  .member-row form{margin:0;}
  .member-row button{font-family:'Inter',sans-serif; font-size:12px; font-weight:600; border:1px solid var(--line); background:#fff; border-radius:999px; padding:5px 11px; cursor:pointer; color:var(--text-dim);}
  .member-row button:hover{border-color:var(--pink-light); color:var(--pink);}
  @media (max-width:680px){ .an-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="an">
  <h1><span class="gtext">Analytics</span></h1>
  <p class="lede">Admin-only — aggregated across every trip on Travel2gether.</p>

  <div class="stat-grid">
    <div class="stat"><div class="num">{{ $totalUsers }}</div><div class="lbl">users</div></div>
    <div class="stat"><div class="num">{{ $totalTrips }}</div><div class="lbl">trips</div></div>
    <div class="stat"><div class="num">{{ $totalSegments }}</div><div class="lbl">flight legs logged</div></div>
    <div class="stat"><div class="num">{{ $resolvedAirlinePct }}%</div><div class="lbl">airlines recognized</div></div>
    <div class="stat"><div class="num">{{ $resolvedAirportPct }}%</div><div class="lbl">airports recognized</div></div>
  </div>

  <div class="an-grid">
    <div>
      <h2>Top airlines</h2>
      <div class="an-card">
        <ul class="an-list">
          @forelse ($topAirlines as $a)
            <li><span>{{ $a->airline_name }} <span class="bar">{{ $a->airline_code }}</span></span><b>{{ $a->uses }}</b></li>
          @empty
            <li class="empty-note" style="display:block; border:none;">No recognized airlines logged yet.</li>
          @endforelse
        </ul>
      </div>
    </div>

    <div>
      <h2>Top routes</h2>
      <div class="an-card">
        <ul class="an-list">
          @forelse ($topRoutes as $r)
            <li><span>{{ $r->from_code }} → {{ $r->to_code }}</span><b>{{ $r->uses }}</b></li>
          @empty
            <li class="empty-note" style="display:block; border:none;">No recognized routes logged yet.</li>
          @endforelse
        </ul>
      </div>
    </div>

    <div>
      <h2>Top departure airports</h2>
      <div class="an-card">
        <ul class="an-list">
          @forelse ($topDepartureAirports as $a)
            <li><span>{{ $a->from_code }}</span><b>{{ $a->uses }}</b></li>
          @empty
            <li class="empty-note" style="display:block; border:none;">No recognized airports logged yet.</li>
          @endforelse
        </ul>
      </div>
    </div>

    <div>
      <h2>Top destinations</h2>
      <div class="an-card">
        <ul class="an-list">
          @forelse ($topDestinations as $d)
            <li><span>{{ $d->destination }}</span><b>{{ $d->uses }}</b></li>
          @empty
            <li class="empty-note" style="display:block; border:none;">No trips yet.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

  <h2>Members</h2>
  @if (session('status'))<div class="flash" style="background:rgba(59,167,118,0.1); border:1px solid #8fd3b4; color:#2f6d54; border-radius:12px; padding:10px 16px; font-size:13.5px; margin-bottom:14px;">{{ session('status') }}</div>@endif
  <div class="an-card">
    @foreach ($members as $m)
      <div class="member-row">
        <div class="who">
          {{ $m->name }}
          <span>{{ $m->email }} · {{ $m->owned_trips_count }} {{ Str::plural('trip', $m->owned_trips_count) }}</span>
        </div>
        @if($m->isAdmin())
          <span class="tier-tag admin">admin</span>
        @else
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="tier-tag {{ $m->subscription }}">{{ $m->subscription }}</span>
            <form method="POST" action="{{ route('analytics.toggle-subscription', $m) }}">
              @csrf @method('PATCH')
              <button type="submit">{{ $m->subscription === 'paid' ? 'Downgrade' : 'Upgrade' }}</button>
            </form>
          </div>
        @endif
      </div>
    @endforeach
  </div>

  <h2>Trips created, by month</h2>
  <div class="an-card">
    <ul class="an-list">
      @forelse ($byMonth as $month => $count)
        <li><span>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</span><b>{{ $count }}</b></li>
      @empty
        <li class="empty-note" style="display:block; border:none;">No trips yet.</li>
      @endforelse
    </ul>
  </div>
</div>
@endsection
