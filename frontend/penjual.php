<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://backend-service/api.php';
$base_url=str_replace("/api.php","",$backend_url);
$u=$_SESSION['user']['username'];
$uid=$_SESSION['user']['id'];
$init=strtoupper(substr($u,0,1));

// Ambil semua produk milik penjual ini
$products=json_decode(file_get_contents("$backend_url?action=get_products&penjual_id=$uid"),true)??[];
// Stats
$stats_raw=json_decode(file_get_contents("$backend_url?action=get_stats&penjual_id=$uid"),true);
$stats=$stats_raw['data']??[];
$per_kat=$stats['per_kategori']??[];
$low=array_filter($products,fn($p)=>$p['stok']<=10&&$p['stok']>0);
$out=array_filter($products,fn($p)=>$p['stok']==0);

$chart_kat_labels=array_column($per_kat,'kategori');
$chart_kat_jml=array_column($per_kat,'jumlah');
$chart_stok_labels=array_map(fn($p)=>mb_strimwidth($p['nama_barang'],0,14,'…'),array_slice($products,0,7));
$chart_stok_data=array_column(array_slice($products,0,7),'stok');
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
<nav class="navbar">
  <div class="nav-inner">
    <a href="penjual.php" class="nav-logo">Naumi<em>Shop</em> <span style="font-size:11px;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:12px;font-weight:400;margin-left:6px">Seller Center</span></a>
    <div style="flex:1"></div>
    <div class="nav-right">
      <a href="pembeli.php" target="_blank" class="nav-icon-btn"><i class="fas fa-eye"></i><span>Toko</span></a>
      <div class="nav-user"><div class="nav-avatar"><?=$init?></div><span class="nav-username"><?=htmlspecialchars($u)?></span></div>
      <a href="logout.php"><button class="nav-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button></a>
    </div>
  </div>
</nav>
<div class="seller-wrap">
<aside class="sidebar">
  <div class="sidebar-profile">
    <div class="sidebar-avatar"><?=$init?></div>
    <h4><?=htmlspecialchars($u)?></h4>
    <p>Seller · <span style="color:#27AE60;font-weight:600">Aktif</span></p>
  </div>
  <div class="sidebar-section">Menu Utama</div>
  <nav class="sidebar-menu">
    <a href="penjual.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="penjual_produk.php"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Info</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="index.php" target="_blank"><i class="fas fa-home"></i> Landing Page</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>
<main class="seller-main">
  <div class="page-header">
    <h1>👋 Halo, <?=htmlspecialchars($u)?>!</h1>
    <p>Pantau performa toko kamu dan kelola produk dari sini.</p>
  </div>

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon si-orange"><i class="fas fa-box"></i></div>
      <div class="stat-info"><h3><?=$stats['total_produk']??0?></h3><p>Total Produk</p></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green"><i class="fas fa-cubes"></i></div>
      <div class="stat-info"><h3><?=number_format($stats['total_stok']??0)?></h3><p>Total Stok</p></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-blue"><i class="fas fa-wallet"></i></div>
      <div class="stat-info">
        <h3>Rp <?=number_format(($stats['nilai_inventori']??0)/1000000,1)?>Jt</h3>
        <p>Nilai Inventori</p>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-purple"><i class="fas fa-tags"></i></div>
      <div class="stat-info"><h3><?=count($per_kat)?></h3><p>Kategori Produk</p></div>
    </div>
  </div>

  <!-- CHARTS -->
  <div class="grid-2 mb-20">
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-chart-bar"></i> Stok per Produk</h3></div>
      <div class="card-body"><canvas id="chartStok" height="220"></canvas></div>
    </div>
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-chart-pie"></i> Produk per Kategori</h3></div>
      <div class="card-body"><canvas id="chartKat" height="220"></canvas></div>
    </div>
  </div>

  <div class="grid-2 mb-20">
    <!-- Peringatan Stok -->
    <div class="card">
      <div class="card-head">
        <h3><i class="fas fa-exclamation-triangle" style="color:#F59E0B"></i> Peringatan Stok</h3>
        <span style="background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600"><?=count($low)+count($out)?> item</span>
      </div>
      <div class="card-body" style="padding:0">
        <?php if(count($low)+count($out)>0):?>
        <table>
          <thead><tr><th>Produk</th><th>Kategori</th><th>Stok</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach(array_merge(iterator_to_array(new ArrayIterator($out)),iterator_to_array(new ArrayIterator($low))) as $p):?>
          <tr>
            <td style="font-weight:500"><?=htmlspecialchars(mb_strimwidth($p['nama_barang'],0,22,'…'))?></td>
            <td><span style="font-size:11px;color:var(--gray)"><?=htmlspecialchars($p['kategori']??'-')?></span></td>
            <td><?=$p['stok']?> pcs</td>
            <td><?=$p['stok']==0?'<span class="badge bg-red">Habis</span>':'<span class="badge bg-yellow">Hampir Habis</span>'?></td>
          </tr>
          <?php endforeach;?>
          </tbody>
        </table>
        <?php else:?>
        <div style="padding:28px;text-align:center;color:var(--gray)">
          <div style="font-size:36px;margin-bottom:8px">✅</div>
          <p style="font-size:13px">Semua stok dalam kondisi aman!</p>
        </div>
        <?php endif;?>
      </div>
    </div>

    <!-- Produk Terbaru -->
    <div class="card">
      <div class="card-head">
        <h3><i class="fas fa-clock"></i> Produk Terbaru</h3>
        <a href="penjual_produk.php" style="font-size:12px;color:var(--primary)">Lihat Semua →</a>
      </div>
      <div class="card-body" style="padding:0">
        <table>
          <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th></tr></thead>
          <tbody>
          <?php if($products):foreach(array_slice($products,0,5) as $p):?>
          <tr>
            <td style="font-weight:500"><?=htmlspecialchars(mb_strimwidth($p['nama_barang'],0,20,'…'))?></td>
            <td><span style="font-size:11px;color:var(--primary)"><?=htmlspecialchars($p['kategori']??'-')?></span></td>
            <td>Rp <?=number_format($p['harga'],0,',','.')?></td>
            <td><?=$p['stok']?></td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--gray)">Belum ada produk</td></tr>
          <?php endif;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="card">
    <div class="card-head"><h3><i class="fas fa-bolt"></i> Aksi Cepat</h3></div>
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap">
      <a href="penjual_tambah.php"><button class="btn-sm btn-add" style="padding:10px 20px"><i class="fas fa-plus"></i> Tambah Produk</button></a>
      <a href="penjual_produk.php"><button class="btn-sm" style="padding:10px 20px;background:#EFF6FF;color:var(--blue);border:1px solid #BFDBFE"><i class="fas fa-list"></i> Kelola Produk</button></a>
      <a href="pembeli.php" target="_blank"><button class="btn-sm" style="padding:10px 20px;background:#ECFDF5;color:var(--green);border:1px solid #A7F3D0"><i class="fas fa-store"></i> Lihat Toko</button></a>
    </div>
  </div>
</main>
</div>
<script>
const COLORS=['#EE4D2D','#FF7337','#3B82F6','#27AE60','#7C3AED','#F59E0B','#EC4899','#14B8A6'];
new Chart(document.getElementById('chartStok'),{
  type:'bar',
  data:{labels:<?=json_encode($chart_stok_labels)?>,datasets:[{label:'Stok',data:<?=json_encode($chart_stok_data)?>,backgroundColor:'rgba(238,77,45,.75)',borderRadius:6,borderSkipped:false}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f5f5f5'}},x:{grid:{display:false}}}}
});
new Chart(document.getElementById('chartKat'),{
  type:'doughnut',
  data:{labels:<?=json_encode($chart_kat_labels)?>,datasets:[{data:<?=json_encode($chart_kat_jml)?>,backgroundColor:COLORS,borderWidth:0,hoverOffset:8}]},
  options:{responsive:true,plugins:{legend:{position:'right',labels:{font:{size:11},boxWidth:14,padding:12}}}}
});
</script>
</body></html>
