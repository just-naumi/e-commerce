<?php
session_start();
$backend_url=getenv('BACKEND_URL')?:'http://backend-service/api.php';
if(isset($_POST['login'])){
  $ch=curl_init("$backend_url?action=login");
  curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>http_build_query($_POST),CURLOPT_RETURNTRANSFER=>true]);
  $res=json_decode(curl_exec($ch),true);curl_close($ch);
  if(($res['status']??'')=='success'){
    $_SESSION['user']=$res['data'];
    header("Location: ".($res['data']['role']=='penjual'?'penjual.php':'pembeli.php'));exit;
  }else{$error='Username atau password salah!';}
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — NaumiShop</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
  <div class="login-left">
    <div class="login-illo">🛍️</div>
    <h1>NaumiShop</h1>
    <p>Platform belanja online terpercaya.<br>Flash Sale setiap hari dengan harga terbaik!</p>
    <div style="display:flex;gap:16px;margin-top:28px;flex-wrap:wrap;justify-content:center">
      <div style="text-align:center"><div style="font-size:26px;font-weight:800">10K+</div><div style="font-size:12px;opacity:.8">Produk</div></div>
      <div style="width:1px;background:rgba(255,255,255,.3)"></div>
      <div style="text-align:center"><div style="font-size:26px;font-weight:800">5K+</div><div style="font-size:12px;opacity:.8">Penjual</div></div>
      <div style="width:1px;background:rgba(255,255,255,.3)"></div>
      <div style="text-align:center"><div style="font-size:26px;font-weight:800">50K+</div><div style="font-size:12px;opacity:.8">Pembeli</div></div>
    </div>
  </div>
  <div class="login-right fade-up">
    <h2>Masuk ke Akun</h2>
    <p class="sub">Selamat datang kembali di NaumiShop! 👋</p>
    <?php if(isset($error)):?>
    <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?=htmlspecialchars($error)?></div>
    <?php endif;?>
    <form method="POST" style="width:100%">
      <div class="form-group">
        <label class="form-label"><i class="fas fa-user" style="color:var(--primary);margin-right:5px"></i>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label"><i class="fas fa-lock" style="color:var(--primary);margin-right:5px"></i>Password</label>
        <input type="password" name="password" id="pwd" class="form-control" placeholder="Masukkan password" required>
      </div>
      <div style="display:flex;align-items:center;gap:7px;margin-bottom:20px">
        <input type="checkbox" id="sp" onchange="document.getElementById('pwd').type=this.checked?'text':'password'">
        <label for="sp" style="font-size:12px;color:var(--gray);cursor:pointer">Tampilkan password</label>
      </div>
      <button type="submit" name="login" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Masuk</button>
    </form>
    <div style="margin-top:16px;text-align:center">
      <a href="index.php" style="color:var(--gray);font-size:12px"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
    </div>
    <div class="demo-box">
      <p>🏪 <strong>Penjual:</strong> Naufal / password123</p>
      <p>🛒 <strong>Pembeli:</strong> Ruth / password123</p>
    </div>
  </div>
</div>
</body></html>
