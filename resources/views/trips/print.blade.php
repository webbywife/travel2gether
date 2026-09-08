@php
    $currency = $trip->currency ?? 'USD';
    $sym = ['USD' => '$', 'PHP' => '₱', 'KRW' => '₩', 'EUR' => '€', 'GBP' => '£'][$currency] ?? ($currency . ' ');
    $budgetByCat = $trip->budgetLines->groupBy('category');
    $budgetTotal = $trip->budgetLines->sum('amount');
    $visa = \App\Support\Destinations::match($trip->destination);
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $trip->title }} — printable scrapbook</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root{
    --pink:#C22A66; --lavender:#7156A8; --text:#241E23; --text-dim:#6B5860;
    --line:rgba(36,30,35,0.14); --paper:#FFFDF7; --rule:#E7DEF0;
  }
  *{box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact;}
  body{
    margin:0; background:#EDE7DD; color:var(--text); font-family:'Inter',sans-serif;
    font-size:15px; line-height:1.6; padding:32px 16px 60px;
  }
  .toolbar{max-width:820px; margin:0 auto 18px; display:flex; justify-content:flex-end; gap:10px;}
  .toolbar button, .toolbar a{
    font-family:'Inter',sans-serif; font-size:13.5px; font-weight:600; color:#fff;
    background:linear-gradient(102deg,#C22A66,#6E54A6); border:none; border-radius:999px;
    padding:9px 18px; cursor:pointer; text-decoration:none; display:inline-block;
  }
  .toolbar a.ghost{background:none; color:var(--text-dim); border:1px solid var(--line);}

  .book{max-width:820px; margin:0 auto;}
  .page{
    position:relative; background:var(--paper); border:1px solid #EAE3D6; border-radius:6px;
    padding:36px 34px; margin-bottom:26px;
    background-image:
      linear-gradient(90deg, transparent 0 46px, rgba(194,42,102,0.22) 46px 47px, transparent 47px),
      repeating-linear-gradient(var(--paper) 0 30px, var(--rule) 30px 31px);
    box-shadow:0 14px 34px -18px rgba(36,30,35,0.28);
  }
  .page::before{
    content:""; position:absolute; left:0; right:0; top:-5px; height:9px;
    background:radial-gradient(circle at 6px 7px, transparent 4.5px, var(--paper) 4.5px) repeat-x;
    background-size:13px 9px;
  }
  .tape{position:absolute; width:88px; height:24px; top:-12px; background:rgba(246,169,198,0.5); border:1px solid rgba(255,255,255,0.4); box-shadow:0 1px 3px rgba(36,30,35,0.12);}
  .tape.l{left:30px; transform:rotate(-5deg);}
  .tape.r{right:30px; transform:rotate(4deg);}

  .cover h1{font-family:'Space Grotesk',sans-serif; font-size:34px; margin:6px 0 4px; letter-spacing:-0.01em;}
  .cover .dest{font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:0.1em; text-transform:uppercase; color:var(--pink);}
  .cover .dates{color:var(--text-dim); margin:8px 0 0;}
  .cover .brand{display:flex; align-items:center; gap:8px; margin-bottom:18px;}
  .cover .brand img{height:30px; width:auto;}
  .cover .brand span{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:16px;}
  .scrap-label{font-family:'Caveat',cursive; font-size:24px; color:var(--pink); transform:rotate(-1.5deg); display:inline-block; margin:0 0 6px;}
  .visa-line{display:inline-block; font-size:12.5px; background:rgba(194,42,102,0.08); color:var(--pink); border-radius:999px; padding:5px 12px; margin-top:12px;}

  .day-head{display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:8px; margin-bottom:14px;}
  .day-head h2{font-family:'Space Grotesk',sans-serif; font-size:22px; margin:0; letter-spacing:-0.01em;}
  .day-head .date{font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--text-dim); letter-spacing:0.06em; text-transform:uppercase;}
  .weather-line{font-size:13px; color:var(--text-dim); margin:-8px 0 14px;}

  .stop{padding:10px 0; border-bottom:1px dashed var(--line);}
  .stop:last-child{border-bottom:none;}
  .stop .time{font-family:'JetBrains Mono',monospace; font-size:12.5px; color:var(--pink); font-weight:600; margin-right:8px;}
  .stop .title{font-weight:600;}
  .stop .desc{color:var(--text-dim); font-size:13.5px; margin-top:3px;}
  .stop .pick{margin-top:6px; font-size:12.5px; background:rgba(113,86,168,0.08); color:var(--lavender); display:inline-block; border-radius:8px; padding:4px 10px;}
  .stop .pick b{color:var(--text);}

  .summary{font-size:14px; color:var(--text-dim); margin-top:16px; padding-top:14px; border-top:1px solid var(--line);}

  .fav-grid{display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:8px;}
  .fav{border:1px solid var(--line); border-radius:10px; padding:12px 14px; background:#fff;}
  .fav .name{font-weight:600;}
  .fav .score{font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--pink);}

  .budget-table{width:100%; border-collapse:collapse; margin-top:8px;}
  .budget-table th, .budget-table td{text-align:left; padding:7px 8px; font-size:13.5px; border-bottom:1px solid var(--line);}
  .budget-table th{font-family:'JetBrains Mono',monospace; font-size:10.5px; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-dim);}
  .budget-table .amt{text-align:right; font-family:'JetBrains Mono',monospace;}
  .cat-row td{font-weight:600; background:rgba(194,42,102,0.04);}
  .grand{font-weight:700;}

  .foot-note{text-align:center; color:var(--text-dim); font-size:12px; margin-top:20px;}

  @media print{
    body{background:#fff; padding:0;}
    .toolbar{display:none;}
    .page{box-shadow:none; border:none; page-break-after:always; margin-bottom:0; border-radius:0;}
    .page:last-child{page-break-after:auto;}
  }
</style>
</head>
<body>

  <div class="toolbar">
    <a class="ghost" href="{{ route('trips.show', $trip) }}">← Back to trip</a>
    <button type="button" onclick="window.print()">🖨 Print / Save as PDF</button>
  </div>

  <div class="book">

    <div class="page cover">
      <div class="tape l"></div><div class="tape r"></div>
      <div class="brand">
        <img src="{{ asset('img/logo-mark.png') }}" alt="">
        <span>Travel2gether</span>
      </div>
      <span class="scrap-label">Our trip, on paper ✂</span>
      <div class="dest">{{ $trip->destination }}</div>
      <h1>{{ $trip->title }}</h1>
      <p class="dates">{{ $trip->start_date->format('M j') }}–{{ $trip->end_date->format('M j, Y') }} · {{ $trip->nights() }} nights @if($trip->party_size) · {{ $trip->party_size }} travelers @endif</p>
      @if($trip->subhead)<p>{{ $trip->subhead }}</p>@endif
      @if($visa)<div class="visa-line">🛂 {{ \App\Support\Destinations::visaLabel($visa['visa_status']) }} for a PH passport — {{ $visa['visa_note'] }}</div>@endif
      @if($trip->hotel_name)<p class="summary">Staying at <b>{{ $trip->hotel_name }}</b>@if($trip->hotel_address) — {{ $trip->hotel_address }}@endif</p>@endif
    </div>

    @foreach($trip->days as $day)
    <div class="page">
      <div class="tape l"></div><div class="tape r"></div>
      <div class="day-head">
        <h2>Day {{ $day->day_number }} — {{ $day->title }}</h2>
        <span class="date">{{ $day->date->format('D · M j, Y') }}</span>
      </div>
      @if($day->weather_note || $day->temp_high)
      <p class="weather-line">
        @if($day->temp_high){{ $day->temp_high }}°@if($day->temp_low)/{{ $day->temp_low }}°C @endif — @endif
        {{ $day->weather_note }}
      </p>
      @endif

      @foreach($day->stops as $stop)
        @php $pick = $stop->options->firstWhere('is_default_pick', true) ?? $stop->options->first(); @endphp
        <div class="stop">
          <div><span class="time">{{ $stop->time }}</span><span class="title">{{ $stop->title }}</span></div>
          @if($stop->description)<div class="desc">{{ $stop->description }}</div>@endif
          @if($pick)<div class="pick">Pick: <b>{{ $pick->name }}</b>@if($pick->costRangeLabel()) · {{ $pick->costRangeLabel() }}@endif</div>@endif
        </div>
      @endforeach

      @if($day->summary)<p class="summary">{{ $day->summary }}</p>@endif
    </div>
    @endforeach

    @if($favorites->isNotEmpty())
    <div class="page">
      <div class="tape l"></div><div class="tape r"></div>
      <span class="scrap-label">Our favorites 💛</span>
      <p class="weather-line" style="margin-top:0;">Places on this trip the group actually recommends.</p>
      <div class="fav-grid">
        @foreach($favorites as $opt)
        <div class="fav">
          <div class="name">{{ $opt->name }}</div>
          @if($opt->note)<div class="desc">{{ $opt->note }}</div>@endif
          <div class="score">👍 {{ $opt->place->upCount() }} recommendation{{ $opt->place->upCount() === 1 ? '' : 's' }}</div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    @if($budgetByCat->isNotEmpty())
    <div class="page">
      <div class="tape l"></div><div class="tape r"></div>
      <span class="scrap-label">Budget worksheet</span>
      <table class="budget-table">
        <thead><tr><th>Item</th><th></th><th class="amt">Amount</th></tr></thead>
        <tbody>
          @foreach($budgetByCat as $category => $lines)
          <tr class="cat-row"><td colspan="2">{{ $category }}</td><td class="amt">{{ $sym }}{{ number_format($lines->sum('amount')) }}</td></tr>
          @foreach($lines as $line)
          <tr>
            <td>{{ $line->label }}</td>
            <td>{{ $line->note }}@if($line->per_person) <span style="color:var(--text-dim)">· per person</span>@endif</td>
            <td class="amt">{{ $sym }}{{ number_format($line->amount) }}</td>
          </tr>
          @endforeach
          @endforeach
          <tr class="grand"><td colspan="2">Total</td><td class="amt">{{ $sym }}{{ number_format($budgetTotal) }}</td></tr>
        </tbody>
      </table>
    </div>
    @endif

  </div>

  <p class="foot-note">Made with Travel2gether · travel2gether.webprvw.xyz</p>

</body>
</html>
