<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://X.X.X.X:30081/api.php';
$base_url=str_replace("/api.php","",$backend_url);
if(isset($_GET['hapus'])){
  $ch=curl_init("$backend_url?action=delete_product");
  curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>["id"=>intval($_GET['hapus'])],CURLOPT_RETURNTRANSFER=>true]);
  curl_exec($ch);curl_close($ch);
  header("Location: penjual_produk.php?deleted=1");exit;
}
$products=json_decode(file_get_contents("$backend_url?action=get_products"),true)??[];
$u=$_SESSION['user']['username'];$init=strtoupper(substr($u,0,1));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Produk Saya — NaumiShop Seller</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a href="penjual.php" class="nav-logo">Naumi<em>Shop</em> <span style="font-size:11px;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:12px;font-weight:400;margin-left:6px">Seller</span></a>
    <div style="flex:1"></div>
    <div class="nav-right">
      <div class="nav-user"><div class="nav-avatar"><?=$init?></div><span class="nav-username"><?=htmlspecialchars($u)?></span></div>
      <a href="logout.php"><button class="nav-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button></a>
    </div>
  </div>
</nav>
<div class="seller-wrap">
<aside class="sidebar">
  <div class="sidebar-profile">
    <div class="sidebar-avatar"><?=$init?></div>
    <h4><?=htmlspecialchars($u)?></h4><p>Seller Account · Aktif</p>
  </div>
  <div class="sidebar-section">Menu Utama</div>
  <nav class="sidebar-menu">
    <a href="penjual.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="penjual_produk.php" class="active"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Lainnya</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="index.php" target="_blank"><i class="fas fa-home"></i> Landing Page</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>
<main class="seller-main">
  <div class="page-header" style="display:flex;align-items:center;justify-content:space-between">
    <div><h1><i class="fas fa-box" style="color:var(--primary)"></i> Produk Saya</h1><p>Kelola seluruh produk yang ada di toko kamu.</p></div>
    <a href="penjual_tambah.php"><button class="btn-sm btn-add" style="padding:10px 18px"><i class="fas fa-plus"></i> Tambah Produk</button></a>
  </div>

  <?php if(isset($_GET['deleted'])):?>
  <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <i class="fas fa-trash"></i> Produk berhasil dihapus.
  </div>
  <?php endif;?>

  <!-- FILTER + SEARCH -->
  <div class="card mb-20">
    <div class="card-body" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <input type="text" id="qprod" class="form-control" style="max-width:300px" placeholder="🔍 Cari nama produk..." oninput="filterTable(this.value)">
      <select id="filter-status" class="form-control" style="max-width:180px" onchange="filterTable(document.getElementById('qprod').value)">
        <option value="">Semua Status</option>
        <option value="aman">Stok Aman (&gt;10)</option>
        <option value="hampir">Hampir Habis (1-10)</option>
        <option value="habis">Habis (0)</option>
      </select>
      <span style="color:var(--gray);font-size:13px" id="count-info"><?=count($products)?> produk ditemukan</span>
    </div>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table id="prod-table">
          <thead><tr><th>#</th><th>Foto</th><th>Nama Produk</th><th>Harga</th><th>Stok</th><th>Nilai Stok</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if($products):$i=1;foreach($products as $p):
            $nilai=$p['harga']*$p['stok'];
            $st=$p['stok']==0?'habis':($p['stok']<=10?'hampir':'aman');
          ?>
          <tr data-name="<?=strtolower(htmlspecialchars($p['nama_barang']))?>" data-st="<?=$st?>">
            <td style="color:var(--gray)"><?=$i++?></td>
            <td>
              <?php if(!empty($p['foto_barang'])):?>
                <img src="<?=$base_url?>/uploads/<?=htmlspecialchars($p['foto_barang'])?>" class="thumb" onerror="this.style.display='none'">
              <?php else:?><div class="thumb-ph"><i class="fas fa-image"></i></div><?php endif;?>
            </td>
            <td style="font-weight:600"><?=htmlspecialchars($p['nama_barang'])?></td>
            <td>Rp <?=number_format($p['harga'],0,',','.')?></td>
            <td><?=$p['stok']?> pcs</td>
            <td>Rp <?=number_format($nilai,0,',','.')?></td>
            <td>
              <?php if($st=='aman'):?><span class="badge bg-green"><i class="fas fa-check-circle"></i> Tersedia</span>
              <?php elseif($st=='hampir'):?><span class="badge bg-yellow"><i class="fas fa-exclamation-circle"></i> Hampir Habis</span>
              <?php else:?><span class="badge bg-red"><i class="fas fa-times-circle"></i> Habis</span><?php endif;?>
            </td>
            <td>
              <a href="?hapus=<?=$p['id']?>" onclick="return confirm('Hapus produk ini?')" class="btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</a>
            </td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="8"><div class="empty"><i class="fas fa-box-open"></i><p>Belum ada produk. <a href="penjual_tambah.php" style="color:var(--primary)">Tambah sekarang</a></p></div></td></tr>
          <?php endif;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
</div>
<script>
function filterTable(q){
  q=q.toLowerCase();
  let st=document.getElementById('filter-status').value;
  let rows=document.querySelectorAll('#prod-table tbody tr[data-name]');
  let vis=0;
  rows.forEach(r=>{
    let match=r.dataset.name.includes(q)&&(st===''||r.dataset.st===st);
    r.style.display=match?'':'none';
    if(match)vis++;
  });
  document.getElementById('count-info').textContent=vis+' produk ditemukan';
}
</script>
</body></html>
