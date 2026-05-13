<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://X.X.X.X:30081/api.php';
$base_url=str_replace("/api.php","",$backend_url);
$products=json_decode(file_get_contents("$backend_url?action=get_products"),true)??[];
$u=$_SESSION['user']['username'];$init=strtoupper(substr($u,0,1));
$total_prod=count($products);
$total_stok=array_sum(array_column($products,'stok'));
$total_nilai=array_sum(array_map(fn($p)=>$p['harga']*$p['stok'],$products));
$low_stock=array_filter($products,fn($p)=>$p['stok']<=10&&$p['stok']>0);
$out_stock=array_filter($products,fn($p)=>$p['stok']==0);
$chart_labels=array_map(fn($p)=>mb_strimwidth($p['nama_barang'],0,16,'…'),array_slice($products,0,8));
$chart_stok=array_column(array_slice($products,0,8),'stok');
$chart_harga=array_column(array_slice($products,0,8),'harga');
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Penjual — NaumiShop</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<!-- TOP NAV -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="penjual.php" class="nav-logo">Naumi<em>Shop</em> <span style="font-size:11px;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:12px;font-weight:400;margin-left:6px">Seller</span></a>
    <div style="flex:1"></div>
    <div class="nav-right">
      <a href="pembeli.php" target="_blank" class="nav-icon-btn"><i class="fas fa-eye"></i><span>Toko</span></a>
      <div class="nav-user">
        <div class="nav-avatar"><?=$init?></div>
        <span class="nav-username"><?=htmlspecialchars($u)?></span>
      </div>
      <a href="logout.php"><button class="nav-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button></a>
    </div>
  </div>
</nav>

<div class="seller-wrap">
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-profile">
    <div class="sidebar-avatar"><?=$init?></div>
    <h4><?=htmlspecialchars($u)?></h4>
    <p>Seller Account · Aktif</p>
  </div>
  <div class="sidebar-section">Menu Utama</div>
  <nav class="sidebar-menu">
    <a href="penjual.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="penjual_produk.php"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Lainnya</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="index.php" target="_blank"><i class="fas fa-home"></i> Landing Page</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>

<!-- MAIN -->
<main class="seller-main">
  <div class="page-header">
    <h1>👋 Halo, <?=htmlspecialchars($u)?>!</h1>
    <p>Selamat datang di Seller Center NaumiShop. Pantau performa toko kamu di sini.</p>
  </div>

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon si-orange"><i class="fas fa-box"></i></div>
      <div class="stat-info"><h3><?=$total_prod?></h3><p>Total Produk</p></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green"><i class="fas fa-cubes"></i></div>
      <div class="stat-info"><h3><?=number_format($total_stok)?></h3><p>Total Stok</p></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-blue"><i class="fas fa-wallet"></i></div>
      <div class="stat-info"><h3>Rp <?=number_format($total_nilai/1000000,1)?>Jt</h3><p>Nilai Inventori</p></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-purple"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="stat-info"><h3><?=count($low_stock)+count($out_stock)?></h3><p>Stok Bermasalah</p></div>
    </div>
  </div>

  <!-- CHARTS ROW -->
  <div class="grid-2 mb-20">
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-chart-bar"></i> Stok per Produk</h3></div>
      <div class="card-body"><canvas id="chartStok" height="220"></canvas></div>
    </div>
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-chart-pie"></i> Distribusi Nilai Produk</h3></div>
      <div class="card-body"><canvas id="chartDist" height="220"></canvas></div>
    </div>
  </div>

  <!-- ALERTS + QUICK INFO -->
  <div class="grid-2 mb-20">
    <!-- Peringatan Stok -->
    <div class="card">
      <div class="card-head">
        <h3><i class="fas fa-exclamation-circle" style="color:#F59E0B"></i> Perhatian Stok</h3>
        <span style="font-size:12px;color:var(--gray)"><?=count($low_stock)+count($out_stock)?> item</span>
      </div>
      <div class="card-body" style="padding:0">
        <?php if(count($low_stock)+count($out_stock)>0):?>
        <table><thead><tr><th>Produk</th><th>Stok</th><th>Status</th></tr></thead><tbody>
        <?php foreach(array_merge(iterator_to_array(new ArrayIterator($out_stock)),iterator_to_array(new ArrayIterator($low_stock))) as $p):?>
        <tr>
          <td style="font-weight:500"><?=htmlspecialchars(mb_strimwidth($p['nama_barang'],0,24,'…'))?></td>
          <td><?=$p['stok']?> pcs</td>
          <td><?=$p['stok']==0?'<span class="badge bg-red">Habis</span>':'<span class="badge bg-yellow">Hampir Habis</span>'?></td>
        </tr>
        <?php endforeach;?>
        </tbody></table>
        <?php else:?>
        <div style="padding:24px;text-align:center;color:var(--gray)">
          <i class="fas fa-check-circle" style="font-size:32px;color:var(--green);display:block;margin-bottom:8px"></i>
          Semua stok dalam kondisi aman ✅
        </div>
        <?php endif;?>
      </div>
    </div>

    <!-- Produk Terbaru -->
    <div class="card">
      <div class="card-head">
        <h3><i class="fas fa-clock"></i> Produk Terbaru</h3>
        <a href="penjual_produk.php" style="font-size:12px;color:var(--primary)">Lihat Semua</a>
      </div>
      <div class="card-body" style="padding:0">
        <table><thead><tr><th>Produk</th><th>Harga</th><th>Stok</th></tr></thead><tbody>
        <?php foreach(array_slice($products,0,5) as $p):?>
        <tr>
          <td style="font-weight:500"><?=htmlspecialchars(mb_strimwidth($p['nama_barang'],0,22,'…'))?></td>
          <td>Rp <?=number_format($p['harga'],0,',','.')?></td>
          <td><?=$p['stok']?></td>
        </tr>
        <?php endforeach;?>
        <?php if(!$products):?>
        <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--gray)">Belum ada produk</td></tr>
        <?php endif;?>
        </tbody></table>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card">
    <div class="card-head"><h3><i class="fas fa-bolt"></i> Aksi Cepat</h3></div>
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="penjual_tambah.php"><button class="btn-sm btn-add"><i class="fas fa-plus"></i> Tambah Produk</button></a>
      <a href="penjual_produk.php"><button class="btn-sm" style="background:#EFF6FF;color:var(--blue);border:1px solid #BFDBFE"><i class="fas fa-list"></i> Kelola Produk</button></a>
      <a href="pembeli.php" target="_blank"><button class="btn-sm" style="background:#ECFDF5;color:var(--green);border:1px solid #A7F3D0"><i class="fas fa-store"></i> Lihat Toko</button></a>
      <a href="index.php" target="_blank"><button class="btn-sm" style="background:#F5F3FF;color:#7C3AED;border:1px solid #DDD6FE"><i class="fas fa-home"></i> Landing Page</button></a>
    </div>
  </div>
</main>
</div>

<script>
const labels=<?=json_encode($chart_labels)?>;
const stoks=<?=json_encode($chart_stok)?>;
const hargas=<?=json_encode($chart_harga)?>;

// Bar Chart — Stok
new Chart(document.getElementById('chartStok'),{
  type:'bar',
  data:{labels,datasets:[{label:'Stok',data:stoks,backgroundColor:'rgba(238,77,45,.8)',borderRadius:6,borderSkipped:false}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f0f0f0'}},x:{grid:{display:false}}}}
});

// Doughnut Chart — Nilai
const colors=['#EE4D2D','#FF7337','#3B82F6','#27AE60','#7C3AED','#F59E0B','#EC4899','#14B8A6'];
new Chart(document.getElementById('chartDist'),{
  type:'doughnut',
  data:{labels,datasets:[{data:hargas,backgroundColor:colors,borderWidth:0,hoverOffset:8}]},
  options:{responsive:true,plugins:{legend:{position:'right',labels:{font:{size:11},boxWidth:14}}}}
});
</script>
</body></html>