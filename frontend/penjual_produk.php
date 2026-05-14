<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://backend-service/api.php';
$u=$_SESSION['user']['username'];$uid=$_SESSION['user']['id'];$init=strtoupper(substr($u,0,1));

if(isset($_GET['hapus'])){
  $ch=curl_init("$backend_url?action=delete_product");
  curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>["id"=>intval($_GET['hapus'])],CURLOPT_RETURNTRANSFER=>true]);
  curl_exec($ch);curl_close($ch);
  header("Location: penjual_produk.php?deleted=1");exit;
}
$filter_kat=$_GET['kat']??'';
$qs="action=get_products&penjual_id=$uid".($filter_kat?"&kategori=".urlencode($filter_kat):'');
$products=json_decode(file_get_contents("$backend_url?$qs"),true)??[];

$CATS=['Semua','Elektronik','Fashion','Makanan','Kecantikan','Olahraga','Rumah Tangga','Lainnya'];
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
    <h4><?=htmlspecialchars($u)?></h4><p>Seller · <span style="color:#27AE60;font-weight:600">Aktif</span></p>
  </div>
  <div class="sidebar-section">Menu Utama</div>
  <nav class="sidebar-menu">
    <a href="penjual.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="penjual_produk.php" class="active"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Info</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>
<main class="seller-main">
  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between">
    <div><h1><i class="fas fa-box" style="color:var(--primary)"></i> Produk Saya</h1><p>Kelola seluruh produk toko kamu.</p></div>
    <a href="penjual_tambah.php"><button class="btn-sm btn-add" style="padding:10px 20px;margin-top:4px"><i class="fas fa-plus"></i> Tambah Produk</button></a>
  </div>

  <?php if(isset($_GET['deleted'])):?>
  <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:8px"><i class="fas fa-trash"></i> Produk berhasil dihapus.</div>
  <?php endif;?>

  <!-- FILTER -->
  <div class="card mb-20" style="margin-bottom:16px">
    <div class="card-body" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <input type="text" id="qprod" class="form-control" style="max-width:280px" placeholder="🔍 Cari nama produk..." oninput="filterTable(this.value)">
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php foreach($CATS as $c):?>
        <a href="?kat=<?=urlencode($c=='Semua'?'':$c)?>"
           style="padding:6px 14px;border-radius:20px;border:1.5px solid <?=($filter_kat==$c||($c=='Semua'&&!$filter_kat))?'var(--primary)':'var(--border)'?>;background:<?=($filter_kat==$c||($c=='Semua'&&!$filter_kat))?'var(--primary)':'#fff'?>;color:<?=($filter_kat==$c||($c=='Semua'&&!$filter_kat))?'#fff':'var(--gray)'?>;font-size:12px;font-weight:500;text-decoration:none;transition:.2s">
          <?=$c?>
        </a>
        <?php endforeach;?>
      </div>
      <span style="color:var(--gray);font-size:12px;margin-left:auto" id="cnt"><?=count($products)?> produk</span>
    </div>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table id="tbl">
          <thead><tr><th>#</th><th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Nilai Stok</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if($products):$i=1;foreach($products as $p):
            $st=$p['stok']==0?'habis':($p['stok']<=10?'hampir':'aman');
            $nilai=$p['harga']*$p['stok'];
          ?>
          <tr data-name="<?=strtolower(htmlspecialchars($p['nama_barang']))?>">
            <td style="color:#bbb;font-size:12px"><?=$i++?></td>
            <td><?php if(!empty($p['foto_base64'])):?><img src="<?=$p['foto_base64']?>" class="thumb" onerror="this.style.display='none'"><?php else:?><div class="thumb-ph"><i class="fas fa-image"></i></div><?php endif;?></td>
            <td style="font-weight:600;max-width:180px"><?=htmlspecialchars($p['nama_barang'])?></td>
            <td><span style="background:#FFF3F0;color:var(--primary);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600"><?=htmlspecialchars($p['kategori']??'—')?></span></td>
            <td>Rp <?=number_format($p['harga'],0,',','.')?></td>
            <td style="font-weight:600"><?=$p['stok']?> pcs</td>
            <td style="color:var(--gray)">Rp <?=number_format($nilai,0,',','.')?></td>
            <td>
              <?php if($st=='aman'):?><span class="badge bg-green"><i class="fas fa-check-circle"></i> Aman</span>
              <?php elseif($st=='hampir'):?><span class="badge bg-yellow"><i class="fas fa-exclamation"></i> Hampir Habis</span>
              <?php else:?><span class="badge bg-red"><i class="fas fa-times"></i> Habis</span><?php endif;?>
            </td>
            <td><a href="?hapus=<?=$p['id']?>&kat=<?=urlencode($filter_kat)?>" onclick="return confirm('Hapus produk ini?')" class="btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</a></td>
          </tr>
          <?php endforeach;else:?>
          <tr><td colspan="9"><div style="text-align:center;padding:48px;color:var(--gray)"><div style="font-size:48px;margin-bottom:12px">📦</div><p>Belum ada produk. <a href="penjual_tambah.php" style="color:var(--primary)">Tambah sekarang</a></p></div></td></tr>
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
  q=q.toLowerCase();let vis=0;
  document.querySelectorAll('#tbl tbody tr[data-name]').forEach(r=>{
    let m=r.dataset.name.includes(q);r.style.display=m?'':'none';if(m)vis++;
  });
  document.getElementById('cnt').textContent=vis+' produk';
}
</script>
</body></html>
