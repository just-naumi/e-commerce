<?php
session_start();
if (!isset($_SESSION['user'])||$_SESSION['user']['role']!='pembeli'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://X.X.X.X:30081/api.php';
$base_url=str_replace("/api.php","",$backend_url);
$products=json_decode(file_get_contents("$backend_url?action=get_products"),true)??[];
$u=$_SESSION['user']['username'];
$init=strtoupper(substr($u,0,1));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>NaumiShop — Flash Sale 🔥</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">Naumi<em>Shop</em></a>
    <div class="nav-search">
      <input type="text" id="q" placeholder="Cari produk Flash Sale..." oninput="filterProd(this.value)">
      <button onclick="filterProd(document.getElementById('q').value)"><i class="fas fa-search"></i></button>
    </div>
    <div class="nav-right">
      <button class="nav-icon-btn"><i class="fas fa-shopping-cart"></i><span>Keranjang</span><span class="nav-badge" id="cart-count">0</span></button>
      <div class="nav-user">
        <div class="nav-avatar"><?=$init?></div>
        <span class="nav-username"><?=htmlspecialchars($u)?></span>
      </div>
      <a href="logout.php"><button class="nav-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button></a>
    </div>
  </div>
</nav>

<div class="buyer-hero">
  <h1>⚡ FLASH SALE</h1>
  <p>Penawaran terbatas — jangan sampai kehabisan!</p>
  <div class="countdown"><div class="cd-box" id="h">00</div><span class="cd-sep">:</span><div class="cd-box" id="m">00</div><span class="cd-sep">:</span><div class="cd-box" id="s">00</div></div>
</div>

<div class="buyer-section">
  <div class="section-bar">
    <h2><i class="fas fa-fire"></i> Produk Flash Sale</h2>
    <span style="font-size:13px;color:var(--gray)"><?=count($products)?> produk</span>
  </div>
  <div class="section-content">
    <div class="product-grid" id="grid">
    <?php if($products):foreach($products as $p):?>
    <div class="product-card fade-up" data-name="<?=strtolower(htmlspecialchars($p['nama_barang']))?>">
      <div class="flash-tag">FLASH SALE</div>
      <?php if(!empty($p['foto_barang'])):?>
        <img src="<?=$base_url?>/uploads/<?=htmlspecialchars($p['foto_barang'])?>" class="pc-img" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="pc-ph" style="display:none"><i class="fas fa-image"></i></div>
      <?php else:?><div class="pc-ph"><i class="fas fa-image"></i></div><?php endif;?>
      <div class="pc-body">
        <div class="pc-name"><?=htmlspecialchars($p['nama_barang'])?></div>
        <div class="pc-price">Rp <?=number_format($p['harga'],0,',','.')?></div>
        <div class="pc-stock">Sisa <?=$p['stok']?> pcs</div>
        <div class="stock-bar"><div class="stock-fill" style="width:<?=min(100,max(5,($p['stok']/100)*100))?>%"></div></div>
        <button class="btn-cart" onclick="addCart('<?=htmlspecialchars($p['nama_barang'])?>')"><i class="fas fa-cart-plus"></i> Beli Sekarang</button>
      </div>
    </div>
    <?php endforeach;else:?>
    <div class="empty" style="grid-column:1/-1"><i class="fas fa-box-open"></i><p>Belum ada produk</p></div>
    <?php endif;?>
    </div>
  </div>
</div>
<div class="toast" id="toast"></div>

<script>
let cart=0;
function startCd(){
  let end=new Date();end.setHours(23,59,59,0);
  setInterval(()=>{
    let d=Math.max(0,Math.floor((end-new Date())/1000));
    document.getElementById('h').textContent=String(Math.floor(d/3600)).padStart(2,'0');
    document.getElementById('m').textContent=String(Math.floor((d%3600)/60)).padStart(2,'0');
    document.getElementById('s').textContent=String(d%60).padStart(2,'0');
  },1000);
}
startCd();
function filterProd(q){
  q=q.toLowerCase();
  document.querySelectorAll('.product-card').forEach(c=>c.style.display=c.dataset.name.includes(q)?'':'none');
}
function addCart(n){
  cart++;document.getElementById('cart-count').textContent=cart;
  showToast('🛒 '+n+' ditambahkan ke keranjang!');
}
function showToast(msg){
  let t=document.getElementById('toast');t.innerHTML='<i class="fas fa-check-circle" style="color:#4ade80"></i> '+msg;
  t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2800);
}
</script>
</body></html>