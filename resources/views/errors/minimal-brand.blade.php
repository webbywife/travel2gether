<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · Travel2gether</title>
<style>
  body{margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif; color:#241E23; background:#FFF8FA; border-top:5px solid #bf2a64;}
  .box{max-width:440px; text-align:center; background:#fff; border:1px solid rgba(36,30,35,.1); border-radius:20px; padding:32px;}
  h1{font-size:24px; margin:10px 0 8px;} p{color:#6B5860; line-height:1.6; margin:0 0 20px;}
  a{display:inline-block; background:linear-gradient(135deg,#C22A66,#8E3A73); color:#fff; text-decoration:none; padding:11px 20px; border-radius:999px; font-weight:600;}
</style></head>
<body><div class="box"><div style="font-size:44px">{{ $emoji }}</div><h1>{{ $title }}</h1><p>{{ $message }}</p><a href="/">Back to Travel2gether</a></div></body></html>
