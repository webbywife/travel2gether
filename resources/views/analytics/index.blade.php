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
