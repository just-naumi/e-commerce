<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='pembeli'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://backend-service/api.php';
$u=$_SESSION['user']['username'];
$init=strtoupper(substr($u,0,1));

$active_cat = $_GET['cat'] ?? 'Semua';
$search_q   = $_GET['q']   ?? '';

$qs = http_build_query(['action'=>'get_products','kategori'=>$active_cat,'search'=>$search_q]);
$products = json_decode(file_get_contents("$backend_url?$qs"), true) ?? [];

$CATEGORIES = [
    ['slug'=>'Semua',    'icon'=>'🏠', 'label'=>'Semua'],
    ['slug'=>'Elektronik','icon'=>'📱','label'=>'Elektronik'],
    ['slug'=>'Fashion',  'icon'=>'👗', 'label'=>'Fashion'],
    ['slug'=>'Makanan',  'icon'=>'🍔', 'label'=>'Makanan'],
    ['slug'=>'Kecantikan','icon'=>'💄','label'=>'Kecantikan'],
    ['slug'=>'Olahraga', 'icon'=>'⚽', 'label'=>'Olahraga'],
    ['slug'=>'Rumah Tangga','icon'=>'🏡','label'=>'Rumah Tangga'],
    ['slug'=>'Lainnya',  'icon'=>'📦', 'label'=>'Lainnya'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>NaumiShop — Belanja Flash Sale</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── BANNER SLIDER ── */
.slider-wrap{position:relative;overflow:hidden;border-radius:12px;background:#f0f0f0;width:100%;height:220px}
.slider-track{display:flex;width:100%;height:100%;transition:transform .5s cubic-bezier(.4,0,.2,1)}
.slider-track img{width:100%;height:100%;min-width:100%;flex-shrink:0;object-fit:cover;display:block}
.slider-btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.85);border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:14px;color:#333;box-shadow:0 2px 8px rgba(0,0,0,.15);transition:.2s;z-index:10}
.slider-btn:hover{background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.2)}
.slider-prev{left:12px}.slider-next{right:12px}
.slider-dots{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);display:flex;gap:6px}
.dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.5);cursor:pointer;transition:.25s}
.dot.active{background:#fff;width:22px;border-radius:4px}

/* ── COUNTDOWN STRIP ── */
.cd-strip{background:var(--grad);padding:12px 0;display:flex;align-items:center;justify-content:center;gap:10px;color:#fff}
.cd-strip span{font-size:13px;font-weight:500}
.cd-strip .cd-box{background:rgba(0,0,0,.25);border-radius:6px;padding:4px 10px;font-size:18px;font-weight:800;min-width:38px;text-align:center}
.cd-strip .sep{font-size:18px;font-weight:800}

/* ── CATEGORY PILLS ── */
.cat-scroll{overflow-x:auto;scrollbar-width:none}
.cat-scroll::-webkit-scrollbar{display:none}
.cat-row{display:flex;gap:8px;padding:4px 2px;width:max-content}
.cat-pill{display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:24px;border:1.5px solid var(--border);background:#fff;cursor:pointer;font-size:13px;font-weight:500;color:var(--gray);transition:all .2s;white-space:nowrap;text-decoration:none}
.cat-pill:hover{border-color:var(--primary);color:var(--primary);background:#FFF3F0}
.cat-pill.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.cat-pill .cat-emoji{font-size:17px}

/* ── PRODUCT CARDS (subtle) ── */
.pc2{background:#fff;border-radius:10px;overflow:hidden;cursor:pointer;transition:box-shadow .22s,transform .22s;border:1px solid #f0f0f0}
.pc2:hover{box-shadow:0 4px 20px rgba(0,0,0,.1);transform:translateY(-3px)}
.pc2-img{width:100%;height:186px;object-fit:cover;display:block;background:#f8f8f8}
.pc2-ph{width:100%;height:186px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;color:#ccc;font-size:44px}
.pc2-body{padding:10px 12px 14px}
.pc2-cat{font-size:10px;color:var(--primary);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
.pc2-name{font-size:13px;font-weight:500;line-height:1.4;height:38px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:6px;color:#333}
.pc2-price{font-size:16px;font-weight:700;color:var(--primary)}
.pc2-meta{display:flex;align-items:center;justify-content:space-between;margin-top:4px;margin-bottom:8px}
.pc2-stock{font-size:11px;color:#999}
.pc2-seller{font-size:11px;color:#bbb;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:90px}
.pc2-btn{width:100%;background:#fff;border:1.5px solid var(--primary);color:var(--primary);border-radius:6px;padding:7px;font-family:'Poppins',sans-serif;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s}
.pc2-btn:hover{background:var(--primary);color:#fff}
.prod-grid2{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}
.section-white{background:#fff;margin-bottom:16px}
.inner{max-width:1300px;margin:0 auto;padding:0 20px}
.sec-hd{display:flex;align-items:center;justify-content:space-between;padding:16px 0 10px}
.sec-hd h2{font-size:16px;font-weight:700;display:flex;align-items:center;gap:7px}
.sec-hd a{font-size:12px;color:var(--primary)}
</style>
</head>
<body>
<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">Naumi<em>Shop</em></a>
    <form class="nav-search" method="GET" action="pembeli.php" style="max-width:520px">
      <input type="hidden" name="cat" value="<?=htmlspecialchars($active_cat)?>">
      <input type="text" name="q" value="<?=htmlspecialchars($search_q)?>" placeholder="Cari produk di NaumiShop...">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div class="nav-right">
      <button class="nav-icon-btn" onclick="showToast('Fitur keranjang segera hadir!')"><i class="fas fa-shopping-cart"></i><span>Keranjang</span><span class="nav-badge" id="cart-count">0</span></button>
      <div class="nav-user">
        <div class="nav-avatar"><?=$init?></div>
        <span class="nav-username"><?=htmlspecialchars($u)?></span>
      </div>
      <a href="logout.php"><button class="nav-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button></a>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT -->
<div class="inner" style="padding-top:20px">

  <!-- BANNER SLIDER -->
  <div class="slider-wrap mb-20" style="margin-bottom:16px">
    <div class="slider-track" id="track">
      <img src="banner1.png" alt="Flash Sale">
      <img src="banner2.png" alt="Gratis Ongkir">
      <img src="banner3.png" alt="Produk Baru">
    </div>
    <button class="slider-btn slider-prev" onclick="slide(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="slider-btn slider-next" onclick="slide(1)"><i class="fas fa-chevron-right"></i></button>
    <div class="slider-dots" id="dots">
      <div class="dot active" onclick="goSlide(0)"></div>
      <div class="dot" onclick="goSlide(1)"></div>
      <div class="dot" onclick="goSlide(2)"></div>
    </div>
  </div>

  <!-- KATEGORI -->
  <div class="section-white" style="border-radius:10px;padding:16px 20px;margin-bottom:16px">
    <div class="sec-hd" style="padding-top:0"><h2><i class="fas fa-th-large" style="color:var(--primary)"></i> Kategori</h2></div>
    <div class="cat-scroll">
      <div class="cat-row">
        <?php foreach($CATEGORIES as $c):?>
        <a href="pembeli.php?cat=<?=urlencode($c['slug'])?>&q=<?=urlencode($search_q)?>"
           class="cat-pill <?=$active_cat==$c['slug']?'active':''?>">
          <span class="cat-emoji"><?=$c['icon']?></span><?=$c['label']?>
        </a>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- PRODUK -->
  <div class="section-white" style="border-radius:10px;padding:16px 20px;margin-bottom:24px">
    <div class="sec-hd" style="padding-top:0">
      <h2><i class="fas fa-fire" style="color:var(--primary)"></i>
        <?=$active_cat=='Semua'?'Semua Produk':$active_cat?>
        <span style="font-size:13px;font-weight:400;color:var(--gray)">(<?=count($products)?>)</span>
      </h2>
      <?php if($active_cat!='Semua'||$search_q):?>
      <a href="pembeli.php">✕ Reset Filter</a>
      <?php endif;?>
    </div>

    <?php if($products):?>
    <div class="prod-grid2">
    <?php foreach($products as $p):?>
    <div class="pc2">
      <?php if(!empty($p['foto_base64'])):?>
        <img src="<?=$p['foto_base64']?>" class="pc2-img" alt=""
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="pc2-ph" style="display:none"><i class="fas fa-image"></i></div>
      <?php else:?><div class="pc2-ph"><i class="fas fa-image"></i></div><?php endif;?>
      <div class="pc2-body">
        <div class="pc2-cat"><?=htmlspecialchars($p['kategori']??'Lainnya')?></div>
        <div class="pc2-name"><?=htmlspecialchars($p['nama_barang'])?></div>
        <div class="pc2-price">Rp <?=number_format($p['harga'],0,',','.')?></div>
        <div class="pc2-meta">
          <span class="pc2-stock"><?=$p['stok']?> pcs tersisa</span>
          <span class="pc2-seller"><?=htmlspecialchars($p['nama_penjual']??'')?></span>
        </div>
        <button class="pc2-btn" onclick="addCart('<?=htmlspecialchars(addslashes($p['nama_barang']))?>')">
          <i class="fas fa-cart-plus"></i> Masukkan Keranjang
        </button>
      </div>
    </div>
    <?php endforeach;?>
    </div>
    <?php else:?>
    <div style="text-align:center;padding:56px 0;color:var(--gray)">
      <div style="font-size:56px;margin-bottom:12px">📦</div>
      <p style="font-size:15px;font-weight:500">Produk tidak ditemukan</p>
      <p style="font-size:13px;margin-top:4px">Coba kategori lain atau hapus filter pencarian</p>
    </div>
    <?php endif;?>
  </div>
</div>

<?php include 'footer.php'; ?>

<div class="toast" id="toast"></div>

<script>
let cur=0,total=3,cart=0,auto;
function goSlide(i){
  cur=i;
  document.getElementById('track').style.transform=`translateX(-${cur*100}%)`;
  document.querySelectorAll('.dot').forEach((d,j)=>d.classList.toggle('active',j===cur));
}
function slide(dir){clearInterval(auto);cur=(cur+dir+total)%total;goSlide(cur);startAuto();}
function startAuto(){auto=setInterval(()=>{cur=(cur+1)%total;goSlide(cur);},4000);}
startAuto();

let c=0;
function addCart(n){
  c++;document.getElementById('cart-count').textContent=c;
  showToast('<i class="fas fa-check-circle" style="color:#4ade80"></i> '+n+' ditambahkan ke keranjang!');
}
function showToast(msg){
  let t=document.getElementById('toast');t.innerHTML=msg;t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2600);
}
</script>
</body></html>
