<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://X.X.X.X:30081/api.php';
$success=$error='';
if(isset($_POST['tambah'])){
  if(empty($_FILES['foto']['tmp_name'])){$error='Foto produk wajib diupload!';}
  elseif(empty($_POST['nama_barang'])||$_POST['harga']<=0){$error='Lengkapi semua data!';}
  else{
    $cfile=new CURLFile($_FILES['foto']['tmp_name'],$_FILES['foto']['type'],$_FILES['foto']['name']);
    $ch=curl_init("$backend_url?action=add_product");
    curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>["nama_barang"=>$_POST['nama_barang'],"harga"=>$_POST['harga'],"stok"=>$_POST['stok'],"penjual_id"=>$_SESSION['user']['id'],"foto"=>$cfile],CURLOPT_RETURNTRANSFER=>true]);
    $res=json_decode(curl_exec($ch),true);curl_close($ch);
    if(($res['status']??'')=='success'){header("Location: penjual_produk.php?added=1");exit;}
    else{$error=$res['message']??'Gagal menambahkan produk';}
  }
}
$u=$_SESSION['user']['username'];$init=strtoupper(substr($u,0,1));
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tambah Produk — NaumiShop Seller</title>
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
    <a href="penjual_produk.php"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php" class="active"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Lainnya</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="index.php" target="_blank"><i class="fas fa-home"></i> Landing Page</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>
<main class="seller-main">
  <div class="page-header">
    <h1><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Tambah Produk Baru</h1>
    <p>Isi semua informasi produk dengan lengkap untuk menarik lebih banyak pembeli.</p>
  </div>

  <?php if($error):?>
  <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <i class="fas fa-exclamation-circle"></i> <?=htmlspecialchars($error)?>
  </div>
  <?php endif;?>

  <div style="max-width:760px">
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-info-circle"></i> Informasi Produk</h3></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="form-prod">
          <div class="form-group">
            <label class="form-label">Nama Produk <span style="color:var(--primary)">*</span></label>
            <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Sepatu Sneakers Putih Premium" required maxlength="100">
            <small style="color:var(--gray);font-size:11px">Maksimal 100 karakter. Gunakan nama yang jelas dan menarik.</small>
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Harga (Rp) <span style="color:var(--primary)">*</span></label>
              <input type="number" name="harga" id="harga" class="form-control" placeholder="cth. 150000" min="0" required oninput="updatePreview()">
              <small style="color:var(--gray);font-size:11px" id="harga-fmt"></small>
            </div>
            <div class="form-group">
              <label class="form-label">Stok <span style="color:var(--primary)">*</span></label>
              <input type="number" name="stok" class="form-control" placeholder="cth. 50" min="0" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Foto Produk <span style="color:var(--primary)">*</span></label>
            <div class="upload-box" id="upload-box">
              <input type="file" name="foto" accept="image/*" required id="foto-inp" onchange="previewFoto(this)">
              <img id="prev" src="" alt="preview">
              <i class="fas fa-cloud-upload-alt" id="up-icon"></i>
              <p id="up-text">Klik atau seret foto ke sini<br><small>JPG, PNG, WEBP · Maks 5MB</small></p>
            </div>
          </div>

          <!-- Preview Card -->
          <div id="prod-preview" style="display:none;margin-bottom:20px">
            <label class="form-label"><i class="fas fa-eye"></i> Preview Produk</label>
            <div style="border:1px solid var(--border);border-radius:var(--r);padding:16px;display:flex;gap:16px;align-items:center;background:#FFFAF9">
              <img id="prev-thumb" src="" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
              <div>
                <div style="font-weight:700;font-size:15px" id="prev-name">—</div>
                <div style="color:var(--primary);font-weight:800;font-size:18px;margin-top:4px" id="prev-price">—</div>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:12px">
            <button type="submit" name="tambah" class="btn-primary" style="max-width:200px">
              <i class="fas fa-plus"></i> Simpan Produk
            </button>
            <a href="penjual_produk.php"><button type="button" class="btn-sm" style="padding:12px 20px;background:var(--gray-light);color:var(--dark)"><i class="fas fa-arrow-left"></i> Kembali</button></a>
          </div>
        </form>
      </div>
    </div>

    <!-- TIPS -->
    <div class="card" style="margin-top:20px">
      <div class="card-head"><h3><i class="fas fa-lightbulb" style="color:#F59E0B"></i> Tips Produk Laris</h3></div>
      <div class="card-body">
        <ul style="list-style:none;display:flex;flex-direction:column;gap:10px">
          <li style="display:flex;gap:10px;font-size:13px"><span style="color:var(--green)">✓</span> Gunakan foto produk yang terang, tajam, dan berlatar putih</li>
          <li style="display:flex;gap:10px;font-size:13px"><span style="color:var(--green)">✓</span> Tulis nama produk yang spesifik dan informatif</li>
          <li style="display:flex;gap:10px;font-size:13px"><span style="color:var(--green)">✓</span> Pastikan harga kompetitif dengan produk serupa</li>
          <li style="display:flex;gap:10px;font-size:13px"><span style="color:var(--green)">✓</span> Perbarui stok secara rutin agar tidak kehabisan</li>
        </ul>
      </div>
    </div>
  </div>
</main>
</div>

<script>
function previewFoto(inp){
  if(inp.files&&inp.files[0]){
    let r=new FileReader();
    r.onload=e=>{
      let img=document.getElementById('prev');
      img.src=e.target.result;img.style.display='block';
      document.getElementById('up-icon').style.display='none';
      document.getElementById('up-text').style.display='none';
      document.getElementById('upload-box').style.borderColor='var(--primary)';
      document.getElementById('prev-thumb').src=e.target.result;
      document.getElementById('prod-preview').style.display='block';
      updatePreview();
    };
    r.readAsDataURL(inp.files[0]);
  }
}
function updatePreview(){
  let h=document.getElementById('harga').value;
  let n=document.querySelector('[name=nama_barang]').value;
  if(h) document.getElementById('harga-fmt').textContent='= Rp '+parseInt(h).toLocaleString('id-ID');
  document.getElementById('prev-name').textContent=n||'Nama Produk';
  document.getElementById('prev-price').textContent=h?'Rp '+parseInt(h).toLocaleString('id-ID'):'—';
}
document.querySelector('[name=nama_barang]').addEventListener('input',updatePreview);
</script>
</body></html>
