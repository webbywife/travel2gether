@php
    /** @var \App\Models\Trip $trip */
    $currency = $trip->currency ?? 'USD';
    $sym = ['USD' => '$', 'PHP' => '₱', 'KRW' => '₩', 'EUR' => '€', 'GBP' => '£'][$currency] ?? ($currency . ' ');

    $budgetByCat = $trip->budgetLines->groupBy('category');

    // Budget category -> slug + colour for the worksheet + doughnut. Known
    // categories keep a fixed colour; anything else (AI-generated trips) gets
    // the next colour from the palette so it still charts.
    $known = [
        'Registration'  => ['slug' => 'reg',     'color' => '#B48FD9'],
        'Rail & entry'  => ['slug' => 'reg',     'color' => '#B48FD9'],
        'Shopping'      => ['slug' => 'shop',    'color' => '#D98FC2'],
        'Meals'         => ['slug' => 'meals',   'color' => '#C77DA2'],
        'Accommodation' => ['slug' => 'accom',   'color' => '#E88FAE'],
        'Insurance'     => ['slug' => 'ins',     'color' => '#C22A66'],
        'Transpo'       => ['slug' => 'transpo', 'color' => '#5B8C7B'],
        'Transport'     => ['slug' => 'transpo', 'color' => '#5B8C7B'],
        'Communication' => ['slug' => 'comm',    'color' => '#8E7986'],
    ];
    $palette = ['#B48FD9', '#D98FC2', '#C77DA2', '#E88FAE', '#5B8C7B', '#8E7986', '#7156A8', '#C2843A'];
    $catMeta = [];
    $i = 0;
    foreach ($budgetByCat->keys() as $cat) {
        $catMeta[$cat] = $known[$cat] ?? ['slug' => \Illuminate\Support\Str::slug($cat) ?: 'cat'.$i, 'color' => $palette[$i % count($palette)]];
        $i++;
    }
    $sequence = $trip->days->pluck('day_number')->map(fn ($n) => (string) $n)->push('budget')->all();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $trip->title }}@if($trip->tagline) — {{ $trip->tagline }}@endif</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}">
<meta name="description" content="{{ $trip->subhead ?? $trip->title }}">
<meta property="og:title" content="{{ $trip->title }}{{ $trip->tagline ? ' — '.$trip->tagline : '' }}">
<meta property="og:description" content="{{ $trip->subhead ?? $trip->title }}">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"
        integrity="sha384-DhxhYObIMeMNGyAG7iK11OHzBIKyEIeRL0ad1iFPAOwZB8iirUlTT0O/WJJUk8+o"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<style>
  :root{
    --bg:#FFFFFF;
    --panel:#FFF8FA;
    --panel-2:#FDEAF1;
    --pink:#C22A66;
    --pink-light:#F6A9C6;
    --accent:#9E4A6E;      /* warm plum — replaces the prototype's gold accent */
    --lavender:#7156A8;
    --text:#3A2E38;
    --text-dim:#6B5860;
    --line: rgba(58,46,56,0.12);
  }
  *{box-sizing:border-box;}
  body{
    margin:0;
    color:var(--text);
    font-family:'Inter', sans-serif;
    font-size:17px;
    line-height:1.6;
    padding:56px 20px 80px;
    background: linear-gradient(rgba(255, 255, 255, 0.62), rgba(255, 255, 255, 0.62)), radial-gradient(circle at 12% 8%, rgba(225, 74, 128, 0.05), transparent 40%), radial-gradient(circle at 88% 18%, rgba(180, 143, 217, 0.05), transparent 40%), url(/img/bg-pattern.jpg), var(--bg);
    background-repeat: repeat-x;
    background-size: 1200px;
    background-position: center, center, center, center top, center;
    background-attachment: fixed, fixed, fixed, fixed, fixed;
    border-top: 5px solid #bf2a64;
  }
  @media (prefers-reduced-motion: reduce){ body{background-attachment:scroll;} }
  .wrap{max-width:860px; margin:0 auto; padding:25px; background:rgb(255 255 255 / 92%); border-radius:10px; border:2px solid var(--pink);}
  .sample-ribbon{
    position:sticky; top:0; z-index:50; display:flex; align-items:center; justify-content:space-between; gap:12px;
    max-width:860px; margin:0 auto 14px; padding:9px 16px; border-radius:0 0 12px 12px;
    background:var(--pink); color:#fff; text-decoration:none; font-size:13px;
    font-family:'JetBrains Mono',monospace; letter-spacing:0.02em;
  }
  .sample-ribbon b{font-weight:700; border-bottom:1px solid rgba(255,255,255,0.6);}
  .sample-ribbon .home{opacity:0.85;}
  @media (max-width:560px){ .sample-ribbon{flex-direction:column; gap:3px; text-align:center;} }

  /* live viewers (Reverb presence) */
  .viewers{display:none; align-items:center; gap:8px; max-width:860px; margin:0 auto 12px; padding:8px 14px;
    background:var(--panel); border:1px solid var(--line); border-radius:999px; font-size:12.5px; color:var(--text-dim);}
  .viewers.on{display:flex;}
  .viewers .dot{width:7px; height:7px; border-radius:50%; background:#3BA776; box-shadow:0 0 0 3px rgba(59,167,118,0.18);}
  .viewers .who{font-family:'JetBrains Mono',monospace; color:var(--text);}
  .viewers .av{display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%;
    background:var(--grad, linear-gradient(102deg,#C22A66,#6E54A6)); color:#fff; font-size:10px; font-weight:700; margin-left:-6px; border:2px solid #fff;}
  .viewers .av:first-of-type{margin-left:4px;}

  /* collaboration bar */
  .collab{max-width:860px; margin:0 auto 14px; display:flex; flex-wrap:wrap; align-items:center; gap:10px 14px;
    padding:10px 16px; background:var(--panel); border:1px solid var(--line); border-radius:12px; font-size:13px;}
  .collab .roster{display:flex; align-items:center;}
  .collab .av{display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%;
    background:linear-gradient(102deg,#C22A66,#6E54A6); color:#fff; font-size:10.5px; font-weight:700; border:2px solid #fff; margin-left:-7px;}
  .collab .av:first-child{margin-left:0;}
  .collab .role-tag{font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.06em; text-transform:uppercase;
    color:var(--text-dim); border:1px solid var(--line); border-radius:999px; padding:2px 8px;}
  .collab .btn-sm{font-family:'Inter',sans-serif; font-size:12.5px; font-weight:600; padding:6px 12px; border-radius:999px;
    border:1px solid var(--pink); color:var(--pink); background:#fff; cursor:pointer; text-decoration:none;}
  .collab .btn-sm.solid{background:linear-gradient(135deg,#C22A66,#8E3A73); color:#fff; border-color:transparent;}
  .collab form{display:inline;}
  .share-panel{max-width:860px; margin:0 auto 14px; border:1px solid var(--line); border-radius:12px; background:#fff; overflow:hidden;}
  .share-panel > summary{list-style:none; cursor:pointer; padding:12px 16px; font-weight:600; font-size:13.5px; color:var(--text);
    background:var(--panel); display:flex; justify-content:space-between; align-items:center;}
  .share-panel > summary::-webkit-details-marker{display:none;}
  .share-panel .body{padding:16px; display:flex; flex-direction:column; gap:16px;}
  .share-panel h4{margin:0 0 8px; font-size:12px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-dim); font-family:'JetBrains Mono',monospace;}
  .share-row{display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:13px;}
  .share-row select, .share-row input{font-family:'Inter',sans-serif; font-size:13px; padding:7px 10px; border:1px solid var(--line); border-radius:8px; background:var(--panel);}
  .share-row input.link{flex:1; min-width:200px; font-family:'JetBrains Mono',monospace; font-size:11.5px; color:var(--text-dim);}
  .share-list{display:flex; flex-direction:column; gap:6px;}
  .share-list .item{display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:8px 10px; border:1px solid var(--line); border-radius:8px; font-size:13px;}
  .share-list .item .grow{flex:1; min-width:120px;}
  .share-list .muted{color:var(--text-dim); font-size:12px;}
  .link-btn{background:none; border:none; color:var(--pink); font:inherit; font-size:12.5px; cursor:pointer; padding:0; text-decoration:underline;}
  .mono{font-family:'JetBrains Mono', monospace;}

  .gt{
    background:linear-gradient(102deg, #C22A66 0%, #A5357A 46%, #6E54A6 100%);
    -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;
  }
  .eyebrow{font-family:'JetBrains Mono', monospace; font-size:13px; letter-spacing:0.18em; color:var(--pink); text-transform:uppercase; margin-bottom:14px; font-weight:500;}
  h1{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:56px; line-height:1.05; margin:0 0 12px; letter-spacing:-0.01em;}
  h1 span{background:linear-gradient(102deg,#C22A66,#A5357A 46%,#6E54A6); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;}
  .title-row{display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;}
  .title-row h1{flex:1; min-width:260px;}
  .print-link{
    display:inline-flex; align-items:center; gap:6px; margin-top:10px; font-size:13.5px; font-weight:500;
    color:var(--text-dim); background:var(--panel); border:1px solid var(--line); border-radius:999px;
    padding:8px 16px; text-decoration:none; white-space:nowrap;
  }
  .print-link:hover{color:var(--pink); border-color:var(--pink-light);}
  @media print{.print-link{display:none;}}
  .subhead{color:var(--text-dim); font-size:18px; max-width:560px; margin:0 0 34px; line-height:1.65;}
  .visa-badge{display:inline-block; font-size:13px; padding:8px 14px; border-radius:999px; margin:0 0 18px; max-width:560px; line-height:1.5;}
  .visa-badge.visa-visa_free{background:rgba(59,167,118,0.12); color:#2f6d54;}
  .visa-badge.visa-evisa{background:rgba(113,86,168,0.12); color:#5a4488;}
  .visa-badge.visa-required{background:rgba(194,42,102,0.08); color:var(--accent);}

  .pass-row{display:flex; gap:10px; margin-bottom:18px;}
  .pass{position:relative; background:var(--panel); border:1px solid var(--line); border-radius:12px; display:flex; overflow:hidden; flex:1; box-shadow:0 2px 10px rgba(58,46,56,0.05);}
  .pass-seg{flex:1; padding:12px 16px;}
  .pass-seg + .pass-seg{border-left:1px dashed rgba(58,46,56,0.18);}
  .pass-route{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:15px; margin:0 0 3px;}
  .pass-route .arrow{color:var(--pink); padding:0 4px;}
  .pass-date{font-family:'JetBrains Mono', monospace; font-size:10.5px; color:var(--accent); margin-bottom:8px;}
  .pass-grid{display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px 10px;}
  .pass-item .k{font-size:9px; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-dim); margin-bottom:1px;}
  .pass-item .v{font-family:'JetBrains Mono', monospace; font-size:11.5px; color:var(--text);}
  .pass-stub{width:74px; background:var(--panel-2); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; padding:10px 6px; text-align:center;}
  .pass-stub .tag{font-family:'JetBrains Mono',monospace; font-size:8.5px; color:var(--text-dim); line-height:1.3;}
  .pass-stub .flight{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:13px; color:var(--pink);}

  .stats{display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin:26px 0 18px;}
  .stat{background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:16px 14px; text-align:center; box-shadow:0 2px 10px rgba(58,46,56,0.05);}
  .stat .num{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:28px; background:linear-gradient(102deg,#C22A66,#A5357A 46%,#6E54A6); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;}
  .stat .lbl{font-size:12.5px; color:var(--text-dim); margin-top:5px;}

  .meaning{margin-top:12px; border-left:2px solid var(--pink); padding:12px 18px; font-size:15px; color:var(--text-dim); background:rgba(225,74,128,0.05); border-radius:0 10px 10px 0;}
  .meaning b{color:var(--text);}
  .hiccup{margin-top:12px; border-left:2px solid var(--accent); padding:12px 18px; font-size:14.5px; color:var(--text-dim); background:rgba(158,74,110,0.06); border-radius:0 10px 10px 0;}
  .hiccup b{color:var(--text);}
  .hiccup ul{margin:8px 0 0; padding-left:18px;}
  .hiccup li{margin-bottom:6px; line-height:1.5;}
  .hiccup li:last-child{margin-bottom:0;}

  .tabbar{display:flex; flex-wrap:wrap; gap:8px; margin:36px 0 0; padding-bottom:4px;}
  .tab{flex:1 1 132px; min-width:120px; background:var(--panel); border:1px solid var(--line); border-radius:12px 12px 0 0; padding:12px 14px; text-align:left; cursor:pointer; font-family:'Inter', sans-serif; color:var(--text-dim); transition:background 0.15s ease, color 0.15s ease;}
  .tab:hover{background:var(--panel-2);}
  .tab .t-num{font-family:'JetBrains Mono', monospace; font-size:11px; letter-spacing:0.05em; text-transform:uppercase; display:block; margin-bottom:3px;}
  .tab .t-title{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:14.5px; color:var(--text); line-height:1.3;}
  .tab.active{background:var(--pink); border-color:var(--pink);}
  .tab.active .t-num, .tab.active .t-title{color:#FFFFFF;}

  .panels{background:var(--panel); border:1px solid var(--line); border-radius:0 14px 14px 14px; padding:26px 26px 30px; box-shadow:0 2px 10px rgba(58,46,56,0.05);}
  .day-panel{display:none;}
  .day-panel.active{display:block;}

  .day-head{margin-bottom:14px;}
  .ai-day{margin:10px 0 4px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
  .ai-btn{font-family:'Inter',sans-serif; font-size:13px; font-weight:600; padding:8px 14px; border-radius:999px;
    border:1px solid var(--pink); background:linear-gradient(135deg,#C22A66,#7156A8); color:#fff; cursor:pointer;}
  .ai-btn:hover{opacity:0.92;}
  .ai-btn:disabled{opacity:0.55; cursor:default;}
  .ai-hint{font-size:11.5px; color:var(--text-dim); font-family:'JetBrains Mono',monospace;}
  .day-date{font-family:'JetBrains Mono', monospace; font-size:13.5px; color:var(--text-dim); letter-spacing:0.06em; text-transform:uppercase;}
  .day-title{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:30px; margin:4px 0 0;}
  .day-title .kr{color:var(--text-dim); font-size:16px; font-weight:500; margin-left:8px;}
  .hotel-note{font-size:13px; color:var(--text-dim); margin:6px 0 0;}
  .hotel-note b{color:var(--text);}

  .weather{display:flex; align-items:center; gap:14px; margin-top:16px; background:var(--panel-2); border:1px solid var(--line); border-radius:12px; padding:14px 18px;}
  .weather .temp{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:22px; color:var(--accent); white-space:nowrap;}
  .weather .temp span{color:var(--pink); font-size:14px; font-weight:500;}
  .weather .wdesc{font-size:14.5px; color:var(--text-dim); line-height:1.55;}
  .weather .wdesc b{color:var(--text); font-weight:600;}
  .weather .wsource{display:block; margin-top:6px; font-family:'JetBrains Mono', monospace; font-size:11.5px; letter-spacing:0.02em; color:var(--text-dim); opacity:0.75;}
  .wtag{display:inline-block; font-family:'JetBrains Mono', monospace; font-size:9px; letter-spacing:0.06em; text-transform:uppercase; color:var(--lavender); border:1px solid var(--lavender); border-radius:999px; padding:2px 8px; margin-left:6px; vertical-align:2px;}

  .outfit-photos{display:flex; gap:10px; margin-top:10px; flex-wrap:wrap;}
  .outfit-photo-item{display:flex; flex-direction:column; align-items:center; gap:5px;}
  .outfit-photo{width:118px; height:118px; border-radius:12px; object-fit:cover; object-position:center; flex-shrink:0; border:1px solid var(--line); cursor:zoom-in; transition:opacity 0.15s ease;}
  .outfit-photo:hover{opacity:0.85;}
  .outfit-photo-label{font-family:'JetBrains Mono', monospace; font-size:10.5px; letter-spacing:0.04em; color:var(--text-dim);}

  .lightbox{display:none; position:fixed; inset:0; background:rgba(20,10,15,0.82); z-index:1000; align-items:center; justify-content:center; padding:24px; cursor:zoom-out;}
  .lightbox.open{display:flex;}
  .lightbox img{max-width:min(600px, 92vw); max-height:88vh; border-radius:14px; box-shadow:0 20px 60px rgba(0,0,0,0.4);}
  .lightbox-close{position:absolute; top:18px; right:22px; width:38px; height:38px; border-radius:50%; border:none; background:rgba(255,255,255,0.15); color:#fff; font-size:20px; line-height:1; cursor:pointer;}
  .lightbox-close:hover{background:rgba(255,255,255,0.28);}

  .outfit{display:flex; gap:8px; flex-wrap:wrap; align-content:flex-start; margin-top:10px;}
  .outfit .chip{font-family:'JetBrains Mono', monospace; font-size:12.5px; color:var(--lavender); border:1px solid var(--lavender); border-radius:999px; padding:5px 12px; white-space:nowrap;}
  @media (max-width:480px){ .outfit-photo{width:88px; height:88px;} }

  .day-media{display:grid; grid-template-columns:1fr; gap:10px; margin-top:16px;}
  .day-media iframe{width:100%; height:220px; border:1px solid var(--line); border-radius:12px;}
  .day-media .cap{display:flex; justify-content:space-between; font-size:12px; color:var(--text-dim); margin-top:-4px;}

  .stops{margin-top:20px; background:var(--bg); border:1px solid var(--line); border-radius:14px; padding:6px 18px;}
  .stop{display:grid; grid-template-columns:76px 1fr; gap:14px; padding:14px 0; border-bottom:1px solid var(--line);}
  .stop:last-child{border-bottom:none;}
  .stop .time{font-family:'JetBrains Mono', monospace; font-size:13.5px; color:var(--accent); padding-top:2px; font-weight:500;}
  .stop .what{font-weight:500; font-size:16.5px; line-height:1.5;}
  .stop .what b{font-weight:600;}
  .stop .note{color:var(--text-dim); font-size:14.5px; margin-top:4px; line-height:1.55;}
  .pin{margin-left:8px; font-size:12px; font-weight:500; color:var(--pink); text-decoration:none; border-bottom:1px dotted var(--pink); white-space:nowrap;}
  .pin:hover{opacity:0.7;}
  .stop .cost{display:inline-block; font-family:'JetBrains Mono', monospace; font-size:12px; font-weight:500; color:var(--pink); background:var(--panel-2); border-radius:999px; padding:2px 9px; margin-left:8px; vertical-align:1px;}
  .stop-hiccup{margin-top:6px; font-size:13px; color:var(--accent);}

  .opt-label{font-family:'JetBrains Mono', monospace; font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:var(--text-dim); margin:10px 0 6px;}
  .opt-grid{display:flex; flex-direction:column; gap:8px;}
  .opt-card{background:var(--bg); border:1px solid var(--line); border-radius:10px; padding:10px 14px 12px; cursor:pointer; transition:border-color 0.15s ease, background 0.15s ease;}
  .opt-card:hover{border-color:var(--pink-light);}
  .opt-card.pick{border-color:var(--pink); background:var(--panel-2);}
  .opt-hint{font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--text-dim); margin:-18px 0 26px;}
  .opt-top{display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;}
  .opt-name{font-weight:600; font-size:15px;}
  .opt-tag{font-family:'JetBrains Mono', monospace; font-size:9px; letter-spacing:0.06em; text-transform:uppercase; color:#fff; background:var(--pink); border-radius:999px; padding:2px 8px; white-space:nowrap;}
  .opt-tag.sponsored{background:var(--lavender);}
  .opt-meta{display:flex; gap:14px; flex-wrap:wrap; font-family:'JetBrains Mono', monospace; font-size:11.5px; color:var(--accent); margin:5px 0 6px;}
  .opt-note{font-size:13.5px; color:var(--text-dim); line-height:1.5;}
  .opt-card .pin{margin-left:0; display:inline-block; margin-top:6px;}

  .rec-row{display:flex; align-items:center; gap:6px; margin-top:8px;}
  .rec-btn{
    display:inline-flex; align-items:center; gap:5px; font-size:12.5px; font-weight:500;
    color:var(--text-dim); background:var(--bg); border:1px solid var(--line); border-radius:999px;
    padding:4px 10px; cursor:pointer; font-family:'Inter', sans-serif; line-height:1.4;
  }
  .rec-btn:hover{border-color:var(--pink-light); color:var(--text);}
  .rec-btn.on{border-color:var(--pink); background:var(--panel-2); color:var(--accent);}
  .rec-btn.down.on{border-color:var(--lavender); background:rgba(113,86,168,0.1); color:var(--lavender);}
  .rec-hint{font-size:11.5px; color:var(--text-dim); font-style:italic;}

  .nav-btns{display:flex; justify-content:space-between; margin-top:22px;}
  .nav-btn{font-family:'Inter', sans-serif; font-size:14px; font-weight:500; color:var(--pink); background:none; border:1px solid var(--pink); border-radius:999px; padding:9px 18px; cursor:pointer;}
  .nav-btn:hover{background:rgba(225,74,128,0.08);}
  .nav-btn:disabled{opacity:0.3; cursor:default;}

  .footer{margin-top:40px; border-top:1px solid var(--line); padding-top:26px; display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;}
  .stamp{font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--pink); border:1px solid var(--pink); border-radius:999px; padding:8px 16px; letter-spacing:0.06em;}
  .tag-line{font-size:14.5px; color:var(--text-dim); max-width:380px; line-height:1.65;}

  .ctrl-row{display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px;}
  .ctrl{background:var(--bg); border:1px solid var(--line); border-radius:12px; padding:10px 16px; display:flex; align-items:center; gap:10px;}
  .ctrl label{font-size:13.5px; color:var(--text-dim); white-space:nowrap;}
  .ctrl input[type=number]{width:68px; background:var(--panel-2); border:1px solid var(--line); border-radius:8px; color:var(--text); font-family:'JetBrains Mono', monospace; font-size:14.5px; padding:7px 9px; text-align:right;}
  .ctrl input[type=number]:focus{outline:1px solid var(--pink);}

  .budget-stats{display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:24px;}
  .bstat{background:var(--bg); border:1px solid var(--line); border-radius:12px; padding:14px; text-align:center;}
  .bstat .num{font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:22px; color:var(--accent);}
  .bstat .lbl{font-size:12px; color:var(--text-dim); margin-top:4px;}

  .budget-grid{display:grid; grid-template-columns:1.3fr 1fr; gap:20px; align-items:start;}
  .bcard{background:var(--bg); border:1px solid var(--line); border-radius:14px; padding:20px;}
  .bcard h3{font-family:'Space Grotesk', sans-serif; font-size:17px; margin:0 0 16px; font-weight:700;}
  .cat{margin-bottom:16px;}
  .cat:last-child{margin-bottom:0;}
  .cat-head{display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;}
  .cat-head .name{font-size:12.5px; letter-spacing:0.04em; text-transform:uppercase; color:var(--pink); font-weight:600;}
  .cat-head .sub{font-family:'JetBrains Mono', monospace; font-size:13.5px; color:var(--text-dim); font-weight:500;}
  .brow{display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid var(--line); gap:10px;}
  .brow:last-child{border-bottom:none;}
  .brow .label{font-size:15px; color:var(--text);}
  .brow .label small{display:block; color:var(--text-dim); font-size:12.5px; margin-top:2px;}
  .brow .amount{display:flex; align-items:center; gap:4px; font-family:'JetBrains Mono', monospace; font-size:15px;}
  .brow .amount span.cur{color:var(--text-dim);}
  .brow input[type=number]{width:84px; background:var(--panel-2); border:1px solid var(--line); border-radius:8px; color:var(--text); font-family:'JetBrains Mono', monospace; font-size:15px; padding:6px 8px; text-align:right;}
  .brow input[type=number]:focus{outline:1px solid var(--pink);}
  .btotal-line{display:flex; justify-content:space-between; align-items:center; margin-top:18px; padding-top:14px; border-top:1px solid var(--line);}
  .btotal-line .t-label{font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:16px;}
  .btotal-line .t-amount{font-family:'JetBrains Mono', monospace; font-weight:700; font-size:23px; color:var(--pink);}
  .chart-wrap{position:relative; height:210px; margin-bottom:14px;}
  .legend{display:flex; flex-direction:column; gap:8px;}
  .legend-item{display:flex; align-items:center; justify-content:space-between; font-size:13.5px;}
  .legend-item .left{display:flex; align-items:center; gap:8px;}
  .dot{width:9px; height:9px; border-radius:50%; flex-shrink:0;}
  .legend-item .pct{font-family:'JetBrains Mono', monospace; color:var(--text-dim);}

  @media (max-width:680px){ .budget-grid{grid-template-columns:1fr;} .budget-stats{grid-template-columns:repeat(2,1fr);} }
  @media (max-width:600px){
    h1{font-size:36px;}
    .pass-row, .pass{flex-direction:column;}
    .pass-seg + .pass-seg{border-left:none; border-top:1px dashed rgba(58,46,56,0.18);}
    .pass-stub{width:100%; flex-direction:row; justify-content:space-between;}
    .stats{grid-template-columns:repeat(2,1fr);}
    .stop{grid-template-columns:56px 1fr;}
    .tab{min-width:96px;}
    .weather{flex-direction:column; align-items:flex-start; gap:6px;}
    .wrap{padding:16px;}
  }
</style>
</head>
<body data-lat="{{ $trip->lat }}" data-lon="{{ $trip->lon }}">
@guest
<a class="sample-ribbon" href="{{ url('/') }}">
  <span class="home">◂ Travel2gether</span>
  <span>Sample itinerary — <b>plan your own, free →</b></span>
</a>
@endguest

@auth
@if(config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
<div class="viewers" id="viewers">
  <span class="dot"></span><span id="viewersText">Connecting…</span>
  <span id="viewersAvatars"></span>
</div>
@endif
@endauth
@php $role = $role ?? null; @endphp

@if (session('status'))
  <div class="collab" style="max-width:860px;background:rgba(59,167,118,0.1);border-color:#8fd3b4;color:#2f6d54;">{{ session('status') }}</div>
@endif
@if (session('error'))
  <div class="collab" style="max-width:860px;background:rgba(225,74,128,0.08);border-color:var(--pink-light);color:var(--accent);">{{ session('error') }}</div>
@endif

@auth
  @if ($role)
    {{-- Members: roster + leave --}}
    <div class="collab">
      <span class="roster">
        @foreach ($trip->members->take(6) as $m)
          <span class="av" title="{{ $m->name }} · {{ $m->pivot->role }}">{{ \Illuminate\Support\Str::of($m->name)->trim()->substr(0,1)->upper() }}</span>
        @endforeach
      </span>
      <span>{{ $trip->members->count() }} {{ \Illuminate\Support\Str::plural('collaborator', $trip->members->count()) }}</span>
      <span class="role-tag">you: {{ $role }}</span>
      <span style="flex:1"></span>
      @can('update', $trip)
        <a class="btn-sm" href="{{ route('trips.edit', $trip) }}">Edit trip details</a>
      @endcan
      @if ($role !== 'owner')
        <form method="POST" action="{{ route('trips.leave', $trip) }}" onsubmit="return confirm('Leave this trip?')">@csrf
          <button type="submit" class="btn-sm">Leave trip</button>
        </form>
      @endif
    </div>

    @can('manageMembers', $trip)
      <details class="share-panel">
        <summary>Share &amp; collaborators <span aria-hidden="true">▾</span></summary>
        <div class="body">
          <div>
            <h4>Create an invite link</h4>
            <form method="POST" action="{{ route('trips.invites.store', $trip) }}" class="share-row">
              @csrf
              <label>Role
                <select name="role">
                  <option value="editor">Editor — vote &amp; edit</option>
                  <option value="viewer">Viewer — read only</option>
                </select>
              </label>
              <label>Expires
                <select name="expires_in_days">
                  <option value="">never</option>
                  <option value="7">in 7 days</option>
                  <option value="30">in 30 days</option>
                </select>
              </label>
              <button type="submit" class="btn-sm solid">Create link</button>
            </form>
          </div>

          @if ($trip->invites->whereNull('revoked_at')->isNotEmpty())
            <div>
              <h4>Active links</h4>
              <div class="share-list">
                @foreach ($trip->invites->whereNull('revoked_at') as $invite)
                  @continue(! $invite->isUsable())
                  <div class="item">
                    <input class="link grow" type="text" readonly value="{{ $invite->url() }}" onclick="this.select()">
                    <span class="role-tag">{{ $invite->role }}</span>
                    <button type="button" class="link-btn" data-copy="{{ $invite->url() }}">copy</button>
                    <span class="muted">{{ $invite->expires_at ? 'expires '.$invite->expires_at->diffForHumans() : 'no expiry' }}</span>
                    <form method="POST" action="{{ route('trips.invites.revoke', [$trip, $invite->token]) }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="link-btn">revoke</button>
                    </form>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <div>
            <h4>People on this trip</h4>
            <div class="share-list">
              @foreach ($trip->members as $m)
                <div class="item">
                  <span class="grow">{{ $m->name }} <span class="muted">{{ $m->email }}</span></span>
                  @if ($m->id === $trip->created_by)
                    <span class="role-tag">owner</span>
                  @else
                    <form method="POST" action="{{ route('trips.members.update', [$trip, $m]) }}" class="share-row" style="gap:6px">
                      @csrf @method('PATCH')
                      <select name="role" onchange="this.form.submit()">
                        <option value="editor" @selected($m->pivot->role === 'editor')>editor</option>
                        <option value="viewer" @selected($m->pivot->role === 'viewer')>viewer</option>
                      </select>
                    </form>
                    <form method="POST" action="{{ route('trips.members.destroy', [$trip, $m]) }}" onsubmit="return confirm('Remove {{ $m->name }}?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="link-btn">remove</button>
                    </form>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </details>
    @endcan
  @elseif ($trip->canView(auth()->user()))
    {{-- Signed in, viewing the public sample — offer a personal copy --}}
    <div class="collab">
      <span>Viewing the sample.</span>
      <span style="flex:1"></span>
      <form method="POST" action="{{ route('trips.duplicate', $trip) }}">@csrf
        <button type="submit" class="btn-sm solid">Make my own copy</button>
      </form>
    </div>
  @endif
@endauth

<div class="wrap">

  @if($trip->origin_label)<div class="eyebrow">Mission briefing · {{ $trip->origin_label }}</div>@endif
  <div class="title-row">
    @php
      // Split the title into "everything but the last word" / "last word" so
      // the last word gets the gradient treatment — but a one-word title (no
      // space to split on) must render once, not duplicated by beforeLast/
      // afterLast both falling back to the whole string.
      $titleHasSpace = str_contains($trip->title, ' ');
    @endphp
    <h1>
      @if($titleHasSpace)
        {{ \Illuminate\Support\Str::beforeLast($trip->title, ' ') }} <span>{{ \Illuminate\Support\Str::afterLast($trip->title, ' ') }}</span>
      @else
        <span>{{ $trip->title }}</span>
      @endif
    </h1>
    <a class="print-link" href="{{ route('trips.print', $trip) }}" target="_blank" rel="noopener">🖨 Print scrapbook</a>
  </div>
  @php $visa = \App\Support\Destinations::match($trip->destination); @endphp
  @if($visa)
    <div class="visa-badge visa-{{ $visa['visa_status'] }}">🛂 {{ \App\Support\Destinations::visaLabel($visa['visa_status']) }} for a PH passport — {{ $visa['visa_note'] }}</div>
  @endif
  @if($trip->subhead)<p class="subhead">{{ $trip->subhead }}</p>@endif
  <p class="opt-hint">Every time slot has 3+ options — tap a card to make it your pick. Choices save automatically.</p>

  @if(is_array($trip->segments) && count($trip->segments))
  <div class="pass-row">
    @foreach($trip->segments as $seg)
    <div class="pass">
      <div class="pass-seg">
        <p class="pass-route">{{ $seg['from'] ?? '' }} <span class="arrow">→</span> {{ $seg['to'] ?? '' }}</p>
        <p class="pass-date">{{ $seg['date'] ?? '' }}</p>
        <div class="pass-grid">
          <div class="pass-item"><div class="k">Depart</div><div class="v">{{ $seg['depart'] ?? '—' }}</div></div>
          <div class="pass-item"><div class="k">Arrive</div><div class="v">{{ $seg['arrive'] ?? '—' }}</div></div>
          <div class="pass-item"><div class="k">Terminal</div><div class="v">{{ $seg['terminal'] ?? '—' }}</div></div>
        </div>
      </div>
      <div class="pass-stub">
        <div class="tag">{{ $seg['airline'] ?? '' }}</div>
        <div class="flight">{{ $seg['flight_no'] ?? '' }}</div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  @if(is_array($trip->stats) && count($trip->stats))
  <div class="stats">
    @foreach($trip->stats as $stat)
    <div class="stat"><div class="num">{{ $stat['value'] ?? '' }}</div><div class="lbl">{{ $stat['label'] ?? '' }}</div></div>
    @endforeach
  </div>
  @endif

  @if($trip->forecast_note)
  <div class="meaning"><b>On the forecast:</b> {{ $trip->forecast_note }} <span id="forecastNoteLive"></span></div>
  @endif

  <div class="tabbar" id="tabbar">
    @foreach($trip->days as $day)
    <button class="tab {{ $loop->first ? 'active' : '' }}" data-day="{{ $day->day_number }}">
      <span class="t-num mono">Day {{ $day->day_number }} · {{ $day->date->format('D j') }}</span>
      <span class="t-title">{{ $day->title }}</span>
    </button>
    @endforeach
    <button class="tab" data-day="budget"><span class="t-num mono">Trip cost</span><span class="t-title">Budget</span></button>
  </div>

  <div class="panels">

    @foreach($trip->days as $day)
    <div class="day-panel {{ $loop->first ? 'active' : '' }}" data-day="{{ $day->day_number }}">
      <div class="day-head">
        <div class="day-date">{{ $day->date->format('D · M j') }}</div>
        <h1 class="day-title">{{ $day->title }}@if($day->title_secondary)<span class="kr">{{ $day->title_secondary }}</span>@endif</h1>
        @if($day->hotel_name && $day->hotel_name !== $trip->hotel_name)
          <div class="hotel-note">🏨 Staying at <b>{{ $day->hotel_name }}</b> for this leg</div>
        @endif

        @if(($aiEnabled ?? false) && ($role === 'owner' || $role === 'editor'))
          <form method="POST" action="{{ route('trips.days.generate', [$trip, $day->id]) }}" class="ai-day"
                onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Drafting…';">
            @csrf
            <button type="submit" class="ai-btn"
                    @if($day->source === 'ai') onclick="return confirm('Re-draft this day? It replaces the current stops.')"
                    @elseif($day->source !== 'skeleton') onclick="return confirm('Draft this day with AI? It replaces the current stops.')" @endif>
              ✨ {{ $day->source === 'ai' ? 'Re-draft' : 'Draft this day' }} with AI
            </button>
            <span class="ai-hint">stops, options, weather &amp; hiccups for {{ $day->area_label ?: $day->title }} on {{ $day->date->format('M j') }}</span>
          </form>
        @endif

        <div class="weather" data-forecast-date="{{ $day->forecast_date?->format('Y-m-d') }}"
             data-lat="{{ $day->lat ?? $trip->lat }}" data-lon="{{ $day->lon ?? $trip->lon }}">
          <div class="temp" data-day-idx="{{ $day->day_number }}">{{ $day->temp_high ? $day->temp_high.'°' : '—' }} <span>@if($day->temp_low)/ {{ $day->temp_low }}°C @endif</span></div>
          <div class="wdesc">
            <b class="wcond">{{ $day->weather_note }}</b>
            @if($day->weather_tag)<span class="wtag">{{ $day->weather_tag }}</span>@endif
            <span class="wsource"></span>
          </div>
        </div>

        @if(is_array($day->outfit_photos) && count($day->outfit_photos))
        <div class="outfit-photos">
          @foreach($day->outfit_photos as $photo)
          <div class="outfit-photo-item">
            <img class="outfit-photo" src="{{ $photo['url'] }}" alt="{{ $photo['alt'] ?? 'Outfit idea' }}" loading="lazy">
            <span class="outfit-photo-label">{{ $photo['label'] ?? '' }}</span>
          </div>
          @endforeach
        </div>
        @endif

        @if(is_array($day->outfit_chips) && count($day->outfit_chips))
        <div class="outfit">
          @foreach($day->outfit_chips as $chip)<span class="chip">{{ $chip }}</span>@endforeach
        </div>
        @endif

        @if($day->map_embed_url)
        <div class="day-media">
          <iframe src="{{ $day->map_embed_url }}" loading="lazy" title="Map of today's stops"></iframe>
          <div class="cap"><span>{{ $day->area_label }}</span><span>Today's area</span></div>
        </div>
        @endif
      </div>

      <div class="stops">
        @foreach($day->stops as $stop)
        <div class="stop">
          <div class="time">{{ $stop->time }}</div>
          <div>
            <div class="what">
              <b>{{ $stop->title }}</b>
              @if($stop->cost_label)<span class="cost">{{ $stop->cost_label }}</span>@endif
              @if($stop->map_url)<a class="pin" href="{{ $stop->map_url }}" target="_blank" rel="noopener">Map ↗</a>@endif
            </div>
            @if($stop->description)<div class="note">{{ $stop->description }}</div>@endif
            @if($stop->hiccup)<div class="stop-hiccup">⚠ {{ $stop->hiccup }}</div>@endif

            @if($stop->has_options && $stop->options->count())
            <div class="opt-label">{{ $stop->option_label ?? 'Options' }}</div>
            <div class="opt-grid">
              @foreach($stop->options as $opt)
              <div class="opt-card {{ $opt->is_default_pick ? 'pick' : '' }}" data-original="{{ $opt->is_default_pick ? 'true' : 'false' }}">
                <div class="opt-top">
                  <span class="opt-name">{{ $opt->name }}</span>
                  @if($opt->is_sponsored)<span class="opt-tag sponsored">Sponsored</span>
                  @elseif($opt->is_default_pick)<span class="opt-tag">Pick</span>@endif
                </div>
                <div class="opt-meta">
                  @if($opt->tier)<span>{{ $opt->tier }}</span>@endif
                  @if($opt->costRangeLabel())<span>{{ $opt->costRangeLabel() }}</span>@endif
                </div>
                @if($opt->note)<div class="opt-note">{{ $opt->note }}</div>@endif
                @if($opt->map_url)<a class="pin" href="{{ $opt->map_url }}" target="_blank" rel="noopener">Map ↗</a>@endif
                @if($opt->place)
                  @php $myVote = $opt->place->voteFor(auth()->user()); @endphp
                  <div class="rec-row" data-place-id="{{ $opt->place_id }}">
                    @auth
                    <button type="button" class="rec-btn up {{ $myVote === 1 ? 'on' : '' }}" data-vote="up">
                      👍 <span class="up-count">{{ $opt->place->upCount() }}</span>
                    </button>
                    <button type="button" class="rec-btn down {{ $myVote === -1 ? 'on' : '' }}" data-vote="down">
                      👎 <span class="down-count">{{ $opt->place->downCount() }}</span>
                    </button>
                    @else
                    <span class="rec-hint">👍 {{ $opt->place->upCount() }} · <a href="{{ route('login') }}">sign in to recommend</a></span>
                    @endauth
                  </div>
                @endif
              </div>
              @endforeach
            </div>
            @endif
          </div>
        </div>
        @endforeach
      </div>

      @if($day->summary)
      <div class="meaning"><b>Why it's worth it:</b> {{ $day->summary }}</div>
      @endif

      @if(is_array($day->hiccups) && count($day->hiccups))
      <div class="hiccup"><b>Possible hiccups:</b>
        <ul>@foreach($day->hiccups as $h)<li>{{ $h }}</li>@endforeach</ul>
      </div>
      @endif

      <div class="nav-btns">
        <button class="nav-btn nav-prev" {{ $loop->first ? 'disabled' : '' }}>← Previous day</button>
        <button class="nav-btn nav-next">Next day →</button>
      </div>
    </div>
    @endforeach

    {{-- ===== Budget ===== --}}
    <div class="day-panel" data-day="budget">
      <div class="day-head">
        <div class="day-date">Trip cost</div>
        <h1 class="day-title">Budget worksheet</h1>
      </div>
      <p style="color:var(--text-dim); font-size:15px; margin:10px 0 20px; line-height:1.6;">Every figure is editable — change any amount and the totals, chart, and per-day numbers update instantly. Your edits stay on this device.</p>

      <div class="ctrl-row">
        <div class="ctrl"><label for="travelers">Travelers</label><input type="number" id="travelers" value="{{ $trip->party_size ?? 1 }}" min="1" step="1"></div>
        <div class="ctrl"><label for="days">Trip length (days)</label><input type="number" id="days" value="{{ $trip->days->count() }}" min="1" step="1"></div>
        <div class="ctrl"><label for="fxrate">{{ $currency }} → PHP rate</label><input type="number" id="fxrate" value="58" min="1" step="0.5"></div>
      </div>

      <div class="budget-stats">
        <div class="bstat"><div class="num mono" id="statTotal">{{ $sym }}0</div><div class="lbl">total (per person)</div></div>
        <div class="bstat"><div class="num mono" id="statTotalPhp">₱0</div><div class="lbl">total in PHP</div></div>
        <div class="bstat"><div class="num mono" id="statGroup">{{ $sym }}0</div><div class="lbl">total, all travelers</div></div>
        <div class="bstat"><div class="num mono" id="statPerDay">{{ $sym }}0</div><div class="lbl">avg. per day</div></div>
      </div>

      <div class="budget-grid">
        <div class="bcard">
          <h3>Line items — per person</h3>
          @foreach($budgetByCat as $category => $lines)
            @php $meta = $catMeta[$category] ?? ['slug' => \Illuminate\Support\Str::slug($category), 'color' => '#8E7986']; @endphp
            <div class="cat">
              <div class="cat-head"><span class="name">{{ $category }}</span><span class="sub" data-sub="{{ $meta['slug'] }}">{{ $sym }}0</span></div>
              @foreach($lines as $line)
              <div class="brow">
                <div class="label">{{ $line->label }}@if($line->note)<small>{{ $line->note }}</small>@endif</div>
                <div class="amount"><span class="cur">{{ $sym }}</span><input type="number" class="item" data-cat="{{ $meta['slug'] }}" value="{{ $line->amount }}" step="1"></div>
              </div>
              @endforeach
            </div>
          @endforeach
          <div class="btotal-line"><span class="t-label">Total per person</span><span class="t-amount mono" id="grandTotal">{{ $sym }}0</span></div>
        </div>

        <div class="bcard">
          <h3>Where it goes</h3>
          <div class="chart-wrap"><canvas id="budgetChart"></canvas></div>
          <div class="legend" id="legend"></div>
        </div>
      </div>

      <div class="nav-btns"><button class="nav-btn nav-prev">← Previous day</button><button class="nav-btn" disabled></button></div>
    </div>

  </div>

  <div class="footer">
    <div class="tag-line">{{ $trip->subhead }}</div>
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
      <button class="nav-btn" id="resetChoices">Reset my choices</button>
      <div class="stamp">{{ \Illuminate\Support\Str::upper($trip->destination) }} · {{ $trip->start_date->format('Y') }}</div>
    </div>
  </div>
</div>

<div class="lightbox" id="lightbox">
  <button class="lightbox-close" id="lightboxClose" aria-label="Close">&times;</button>
  <img id="lightboxImg" src="" alt="">
</div>

<script>
  window.T2G = {
    slug: @json($trip->slug),
    currencySymbol: @json($sym),
    sequence: @json($sequence),
    catMeta: @json(collect($catMeta)->mapWithKeys(fn ($m, $k) => [$m['slug'] => ['label' => $k, 'color' => $m['color']]])),
  };
</script>
<script>
(function () {
  const T = window.T2G;

  /* ===== Day tabs + nav ===== */
  const tabs = Array.from(document.querySelectorAll('.tab'));
  const panels = Array.from(document.querySelectorAll('.day-panel'));

  function showDay(id, opts = {}) {
    id = String(id);
    if (!T.sequence.includes(id)) return;
    tabs.forEach(t => t.classList.toggle('active', t.dataset.day === id));
    panels.forEach(p => p.classList.toggle('active', p.dataset.day === id));
    if (history.replaceState) history.replaceState(null, '', '#' + (id === T.sequence[0] ? '' : id));
    if (opts.scroll === false) return;
    document.querySelector('.tabbar .tab.active')?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    const bar = document.querySelector('.tabbar');
    if (bar) window.scrollTo({ top: bar.offsetTop - 20, behavior: 'smooth' });
  }

  tabs.forEach(t => t.addEventListener('click', () => showDay(t.dataset.day)));
  window.addEventListener('hashchange', () => showDay(location.hash.replace('#', '') || T.sequence[0], { scroll: false }));
  if (location.hash) showDay(location.hash.replace('#', ''), { scroll: false });

  panels.forEach(panel => {
    const cur = panel.dataset.day;
    const idx = T.sequence.indexOf(cur);
    panel.querySelector('.nav-prev')?.addEventListener('click', () => { if (idx > 0) showDay(T.sequence[idx - 1]); });
    panel.querySelector('.nav-next')?.addEventListener('click', () => { if (idx < T.sequence.length - 1) showDay(T.sequence[idx + 1]); });
  });

  /* ===== Tap-to-pick options (per-device, per-trip) ===== */
  (function () {
    const KEY = 't2g:' + T.slug + ':choices:v1';
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { saved = {}; }

    const grids = Array.from(document.querySelectorAll('.opt-grid'));

    function selectCard(grid, card, persist) {
      grid.querySelectorAll('.opt-card').forEach(c => {
        c.classList.remove('pick');
        if (c.dataset.original !== 'true') c.querySelector('.opt-tag')?.remove();
      });
      card.classList.add('pick');
      if (!card.querySelector('.opt-tag')) {
        const tag = document.createElement('span');
        tag.className = 'opt-tag';
        tag.textContent = card.dataset.original === 'true' ? 'Pick' : 'Your pick';
        card.querySelector('.opt-top').appendChild(tag);
      } else {
        card.querySelector('.opt-tag').textContent = card.dataset.original === 'true' ? 'Pick' : 'Your pick';
      }
      if (persist) {
        saved[grids.indexOf(grid)] = Array.from(grid.children).indexOf(card);
        try { localStorage.setItem(KEY, JSON.stringify(saved)); } catch (e) {}
      }
    }

    grids.forEach((grid, gi) => {
      Array.from(grid.children).forEach(card => {
        card.addEventListener('click', () => selectCard(grid, card, true));
      });
      if (saved[gi] !== undefined && grid.children[saved[gi]]) selectCard(grid, grid.children[saved[gi]], false);
    });

    document.getElementById('resetChoices')?.addEventListener('click', () => {
      try { localStorage.removeItem(KEY); } catch (e) {}
      location.reload();
    });
  })();

  /* ===== Thumbs up/down on places (signed-in users) ===== */
  (function () {
    const csrf = @json(csrf_token());
    document.querySelectorAll('.rec-row').forEach(row => {
      row.querySelectorAll('.rec-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          e.preventDefault();
          e.stopPropagation();
          if (btn.disabled) return;
          btn.disabled = true;
          try {
            const res = await fetch(`/places/${row.dataset.placeId}/recommend`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              body: JSON.stringify({ vote: btn.dataset.vote }),
            });
            if (!res.ok) throw new Error('vote failed');
            const data = await res.json();
            row.querySelector('.up .up-count').textContent = data.up;
            row.querySelector('.down .down-count').textContent = data.down;
            row.querySelector('.up').classList.toggle('on', data.my_vote === 1);
            row.querySelector('.down').classList.toggle('on', data.my_vote === -1);
          } catch (e) { /* silent — thumbs are a nice-to-have, not critical path */ }
          btn.disabled = false;
        });
      });
    });
  })();

  /* ===== Budget worksheet ===== */
  const fmt = n => T.currencySymbol + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  const fmtPhp = n => '₱' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  const catKeys = Object.keys(T.catMeta);

  let chart = null;
  const canvas = document.getElementById('budgetChart');
  try {
    if (typeof Chart === 'undefined') throw new Error('Chart.js unavailable');
    chart = new Chart(canvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: catKeys.map(k => T.catMeta[k].label),
        datasets: [{
          data: catKeys.map(() => 0),
          backgroundColor: catKeys.map(k => T.catMeta[k].color),
          borderColor: '#FFF8FA', borderWidth: 3,
        }],
      },
      options: { cutout: '68%', plugins: { legend: { display: false }, tooltip: { enabled: true } } },
    });
  } catch (e) {
    if (canvas) canvas.style.display = 'none';
    const p = document.createElement('p');
    p.style.cssText = 'font-size:13.5px; color:var(--text-dim);';
    p.textContent = 'Chart could not load — figures below are still accurate.';
    canvas?.parentElement.insertBefore(p, canvas);
  }

  function recalc() {
    const travelers = parseFloat(document.getElementById('travelers').value) || 1;
    const days = parseFloat(document.getElementById('days').value) || 1;
    const fx = parseFloat(document.getElementById('fxrate').value) || 58;

    const totals = {};
    catKeys.forEach(k => totals[k] = 0);
    document.querySelectorAll('.item').forEach(inp => {
      const c = inp.dataset.cat;
      if (!(c in totals)) totals[c] = 0;
      totals[c] += parseFloat(inp.value) || 0;
    });

    document.querySelectorAll('[data-sub]').forEach(el => {
      el.textContent = fmt(totals[el.dataset.sub] || 0);
    });

    const grand = Object.values(totals).reduce((a, b) => a + b, 0);
    document.getElementById('grandTotal').textContent = fmt(grand);
    document.getElementById('statTotal').textContent = fmt(grand);
    document.getElementById('statTotalPhp').textContent = fmtPhp(grand * fx);
    document.getElementById('statGroup').textContent = fmt(grand * travelers);
    document.getElementById('statPerDay').textContent = fmt(grand / days);

    if (chart) {
      chart.data.datasets[0].data = catKeys.map(k => totals[k] || 0);
      chart.update();
    }

    const legend = document.getElementById('legend');
    legend.innerHTML = '';
    catKeys.forEach(k => {
      const pct = grand > 0 ? Math.round((totals[k] / grand) * 100) : 0;
      const item = document.createElement('div');
      item.className = 'legend-item';
      item.innerHTML = `<div class="left"><span class="dot" style="background:${T.catMeta[k].color}"></span>${T.catMeta[k].label}</div><span class="pct mono">${fmt(totals[k] || 0)} · ${pct}%</span>`;
      legend.appendChild(item);
    });
  }

  document.querySelectorAll('.item, #travelers, #days, #fxrate').forEach(el => el.addEventListener('input', recalc));
  recalc();

  /* ===== Live weather (Open-Meteo, no key) — per day's own area ===== */
  (function () {
    const noteEl = document.getElementById('forecastNoteLive');
    const panels = Array.from(document.querySelectorAll('.weather[data-forecast-date]'))
      .filter(p => p.dataset.forecastDate && p.dataset.lat && p.dataset.lon);
    if (!panels.length) return;

    const WMO = {0:'clear skies',1:'mostly clear',2:'partly cloudy',3:'overcast',45:'foggy',48:'foggy',51:'light drizzle',53:'drizzle',55:'heavy drizzle',61:'light rain',63:'rain',65:'heavy rain',71:'light snow',73:'snow',75:'heavy snow',80:'rain showers',81:'rain showers',82:'heavy rain showers',95:'thunderstorms',96:'thunderstorms with hail',99:'thunderstorms with hail'};
    const adj = c => c >= 31 ? 'Hot' : c >= 27 ? 'Warm' : c >= 22 ? 'Mild' : 'Cool';

    // group panels by rounded lat/lon so we make one call per distinct area
    const groups = {};
    panels.forEach(p => {
      const key = (+p.dataset.lat).toFixed(2) + ',' + (+p.dataset.lon).toFixed(2);
      (groups[key] = groups[key] || { lat: +p.dataset.lat, lon: +p.dataset.lon, panels: [] }).panels.push(p);
    });

    let anyLive = false;
    Promise.all(Object.values(groups).map(g => {
      const dates = g.panels.map(p => p.dataset.forecastDate).sort();
      const url = `https://api.open-meteo.com/v1/forecast?latitude=${g.lat}&longitude=${g.lon}` +
        `&daily=weathercode,temperature_2m_max,temperature_2m_min,precipitation_probability_max` +
        `&timezone=auto&start_date=${dates[0]}&end_date=${dates[dates.length - 1]}`;
      return fetch(url).then(r => r.ok ? r.json() : null).then(data => {
        if (!data || !data.daily || !Array.isArray(data.daily.time)) return;
        const by = {};
        data.daily.time.forEach((d, i) => by[d] = {
          max: data.daily.temperature_2m_max[i], min: data.daily.temperature_2m_min[i],
          code: data.daily.weathercode[i], rain: data.daily.precipitation_probability_max[i],
        });
        g.panels.forEach(panel => {
          const d = by[panel.dataset.forecastDate];
          if (!d || d.max == null) return;
          anyLive = true;
          panel.querySelector('.temp').innerHTML = `${Math.round(d.max)}° <span>/ ${Math.round(d.min)}°C</span>`;
          const rain = d.rain != null ? `, ~${Math.round(d.rain)}% rain chance` : '';
          panel.querySelector('.wcond').textContent = `${adj(d.max)}, ${WMO[d.code] || 'mixed conditions'}${rain}.`;
          panel.querySelector('.wsource').textContent = 'Live forecast · this day\'s area';
        });
      }).catch(() => {});
    })).then(() => {
      if (noteEl) noteEl.textContent = anyLive
        ? 'Days inside the ~16-day window show a live forecast for that day\'s actual area.'
        : 'These dates are outside the live ~16-day window — showing the planned notes instead.';
    });
  })();

  /* ===== Outfit photo lightbox ===== */
  (function () {
    const lb = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    const close = () => { lb.classList.remove('open'); img.src = ''; };
    document.querySelectorAll('.outfit-photo').forEach(el => el.addEventListener('click', () => {
      img.src = el.src.replace(/([?&])w=\d+/, '$1w=1200');
      img.alt = el.alt;
      lb.classList.add('open');
    }));
    lb.addEventListener('click', close);
    document.getElementById('lightboxClose').addEventListener('click', e => { e.stopPropagation(); close(); });
    img.addEventListener('click', e => e.stopPropagation());
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  })();

  /* ===== Copy invite link ===== */
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy');
      (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject())
        .then(function () { var o = btn.textContent; btn.textContent = 'copied ✓'; setTimeout(function () { btn.textContent = o; }, 1500); })
        .catch(function () { var i = btn.closest('.item')?.querySelector('input.link'); if (i) { i.select(); document.execCommand('copy'); } });
    });
  });
})();
</script>
@auth
@if(config('broadcasting.default') === 'reverb' && config('broadcasting.connections.reverb.key'))
@php $rev = config('broadcasting.connections.reverb'); @endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.4.0/pusher.min.js"
        integrity="sha384-F0kpynobTwTZnUdhUu3W6P6QcdLW2hKKDTh6ECx+p/osH1JYIKlYqUjNc4ObdiTj"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"
        integrity="sha384-kXMb9TeRl71+fbBi+MgXNRaz6hQlxkKuTq+ZQ3V2KGShvN07Ge/fsCNce1mb/pCB"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
  if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') return;
  window.Pusher = Pusher;

  var echo = new Echo({
    broadcaster: 'reverb',
    key: @json($rev['key']),
    wsHost: @json($rev['options']['host']),
    wsPort: {{ (int) ($rev['options']['port'] ?? 8080) }},
    wssPort: {{ (int) ($rev['options']['port'] ?? 8080) }},
    forceTLS: @json(($rev['options']['scheme'] ?? 'http') === 'https'),
    enabledTransports: ['ws', 'wss'],
  });

  var box = document.getElementById('viewers');
  var txt = document.getElementById('viewersText');
  var avs = document.getElementById('viewersAvatars');
  var me = @json(auth()->id());

  function render(users) {
    var others = users.filter(function (u) { return u.id !== me; });
    if (!others.length) { box.classList.remove('on'); return; }
    box.classList.add('on');
    txt.textContent = others.length === 1
      ? others[0].name + ' is also here'
      : others.length + ' others are here';
    avs.innerHTML = others.slice(0, 5).map(function (u) {
      return '<span class="av" title="' + (u.name || '') + '">' + (u.name || '?').trim().charAt(0).toUpperCase() + '</span>';
    }).join('');
  }

  var present = [];
  echo.join('trip.' + @json($trip->slug))
    .here(function (users) { present = users; render(present); })
    .joining(function (user) { present.push(user); render(present); })
    .leaving(function (user) { present = present.filter(function (u) { return u.id !== user.id; }); render(present); })
    .listen('.trip.activity', function (e) {
      // Phase 2b: reconcile live picks / budget / stop edits here.
      document.dispatchEvent(new CustomEvent('trip:activity', { detail: e }));
    })
    .error(function () { box.classList.remove('on'); });
})();
</script>
@endif
@endauth
{{-- Skip Trippie on the public samples — this page also renders inside the
     landing page's live-preview iframe, where a floating chat widget has
     nowhere sensible to sit. Real (owned/shared) trips still get Trippie. --}}
@unless($trip->is_public && $trip->created_by === null)
  @include('partials.trippie')
  @include('partials.cookie-notice')
@endunless
</body>
</html>
