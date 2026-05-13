<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='penjual'){header("Location: login.php");exit;}
$backend_url=getenv('BACKEND_URL')?:'http://backend-service/api.php';
$u=$_SESSION['user']['username'];$uid=$_SESSION['user']['id'];$init=strtoupper(substr($u,0,1));
$error='';

if(isset($_POST['tambah'])){
  if(empty(trim($_POST['nama_barang']))||intval($_POST['harga'])<=0){
    $error='Lengkapi semua data (nama & harga wajib)!';
  } else {
    $cfile=!empty($_FILES['foto']['tmp_name'])
      ? new CURLFile($_FILES['foto']['tmp_name'],$_FILES['foto']['type'],$_FILES['foto']['name'])
      : null;
    if(!$cfile){$error='Foto produk wajib diupload!';}
    else{
      $ch=curl_init("$backend_url?action=add_product");
      curl_setopt_array($ch,[
        CURLOPT_POST=>1,
        CURLOPT_POSTFIELDS=>[
          "nama_barang"=>trim($_POST['nama_barang']),
          "harga"=>intval($_POST['harga']),
          "stok"=>intval($_POST['stok']),
          "kategori"=>$_POST['kategori']??'Lainnya',
          "deskripsi"=>trim($_POST['deskripsi']??''),
          "penjual_id"=>$uid,
          "foto"=>$cfile
        ],
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_TIMEOUT=>15
      ]);
      $raw=curl_exec($ch);
      $curl_err=curl_error($ch);
      curl_close($ch);
      if($raw===false){
        $error='Tidak dapat terhubung ke backend API. ('.$curl_err.')';
      } else {
        $res=json_decode($raw,true);
        if(($res['status']??'')=='success'){header("Location: penjual_produk.php?added=1");exit;}
        else{$error=$res['message']??'Gagal menambahkan produk (response tidak dikenal)';}
      }
    }
  }
}
$CATS=['Elektronik','Fashion','Makanan','Kecantikan','Olahraga','Rumah Tangga','Lainnya'];
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
  <div class="sidebar-profile"><div class="sidebar-avatar"><?=$init?></div><h4><?=htmlspecialchars($u)?></h4><p>Seller · <span style="color:#27AE60;font-weight:600">Aktif</span></p></div>
  <div class="sidebar-section">Menu Utama</div>
  <nav class="sidebar-menu">
    <a href="penjual.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="penjual_produk.php"><i class="fas fa-box"></i> Produk Saya</a>
    <a href="penjual_tambah.php" class="active"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
  </nav>
  <div class="sidebar-section">Info</div>
  <nav class="sidebar-menu">
    <a href="pembeli.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
    <a href="logout.php" style="color:#EF4444!important"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </nav>
</aside>
<main class="seller-main">
  <div class="page-header">
    <h1><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Tambah Produk Baru</h1>
    <p>Isi informasi produk dengan lengkap untuk menarik lebih banyak pembeli.</p>
  </div>

  <?php if($error):?>
  <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:8px"><i class="fas fa-exclamation-circle"></i><?=htmlspecialchars($error)?></div>
  <?php endif;?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
    <!-- FORM -->
    <div class="card">
      <div class="card-head"><h3><i class="fas fa-info-circle"></i> Informasi Produk</h3></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="fprod">
          <div class="form-group">
            <label class="form-label">Nama Produk <span style="color:var(--primary)">*</span></label>
            <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Sepatu Sneakers Putih Premium" required maxlength="100" oninput="livePrev()">
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Harga (Rp) <span style="color:var(--primary)">*</span></label>
              <input type="number" name="harga" id="harga" class="form-control" placeholder="cth. 150000" min="1" required oninput="livePrev()">
            </div>
            <div class="form-group">
              <label class="form-label">Stok <span style="color:var(--primary)">*</span></label>
              <input type="number" name="stok" class="form-control" placeholder="cth. 50" min="0" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Kategori <span style="color:var(--primary)">*</span></label>
            <select name="kategori" class="form-control" required onchange="livePrev()">
              <?php foreach($CATS as $c):?><option value="<?=$c?>"><?=$c?></option><?php endforeach;?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Deskripsi Produk</label>
            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan produk secara singkat: bahan, ukuran, keunggulan, dll." style="resize:vertical"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Foto Produk <span style="color:var(--primary)">*</span></label>
            <div class="upload-box" id="upbox">
              <input type="file" name="foto" accept="image/*" required id="foto-inp" onchange="previewFoto(this)">
              <img id="prev" src="" alt="preview">
              <i class="fas fa-cloud-upload-alt" id="upico"></i>
              <p id="uptxt">Klik atau seret foto ke sini<br><small style="color:#bbb">JPG, PNG, WEBP · Maks 5MB</small></p>
            </div>
          </div>
          <div style="display:flex;gap:12px">
            <button type="submit" name="tambah" class="btn-primary" style="max-width:200px"><i class="fas fa-plus"></i> Simpan Produk</button>
            <a href="penjual_produk.php"><button type="button" class="btn-sm" style="padding:12px 20px;background:var(--gray-light);color:var(--dark)"><i class="fas fa-arrow-left"></i> Kembali</button></a>
          </div>
        </form>
      </div>
    </div>

    <!-- SIDEBAR KANAN -->
    <div style="display:flex;flex-direction:column;gap:16px">
      <!-- Preview Card -->
      <div class="card">
        <div class="card-head"><h3><i class="fas fa-eye"></i> Preview Produk</h3></div>
        <div class="card-body" style="padding:12px">
          <div style="border:1px solid #f0f0f0;border-radius:8px;overflow:hidden">
            <div id="prev-img-wrap" style="height:140px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:40px"><i class="fas fa-image"></i></div>
            <div style="padding:10px 12px">
              <div style="font-size:10px;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px" id="prev-cat">Kategori</div>
              <div style="font-size:13px;font-weight:500;margin-bottom:6px;color:#333;line-height:1.4" id="prev-name">Nama Produk</div>
              <div style="font-size:17px;font-weight:800;color:var(--primary)" id="prev-price">Rp —</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Tips -->
      <div class="card">
        <div class="card-head"><h3><i class="fas fa-lightbulb" style="color:#F59E0B"></i> Tips Produk Laris</h3></div>
        <div class="card-body" style="padding:14px 16px">
          <ul style="list-style:none;display:flex;flex-direction:column;gap:9px">
            <li style="font-size:12px;display:flex;gap:8px;align-items:flex-start"><span style="color:var(--green);flex-shrink:0">✓</span> Foto terang, tajam, latar putih</li>
            <li style="font-size:12px;display:flex;gap:8px;align-items:flex-start"><span style="color:var(--green);flex-shrink:0">✓</span> Nama produk spesifik & informatif</li>
            <li style="font-size:12px;display:flex;gap:8px;align-items:flex-start"><span style="color:var(--green);flex-shrink:0">✓</span> Harga kompetitif sesuai pasar</li>
            <li style="font-size:12px;display:flex;gap:8px;align-items:flex-start"><span style="color:var(--green);flex-shrink:0">✓</span> Pilih kategori yang tepat</li>
            <li style="font-size:12px;display:flex;gap:8px;align-items:flex-start"><span style="color:var(--green);flex-shrink:0">✓</span> Deskripsi lengkap meningkatkan kepercayaan</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>
</div>
<script>
function previewFoto(inp){
  if(inp.files&&inp.files[0]){
    let r=new FileReader();r.onload=e=>{
      let p=document.getElementById('prev');p.src=e.target.result;p.style.display='block';
      document.getElementById('upico').style.display='none';
      document.getElementById('uptxt').style.display='none';
      document.getElementById('upbox').style.borderColor='var(--primary)';
      let w=document.getElementById('prev-img-wrap');
      w.innerHTML='<img src="'+e.target.result+'" style="width:100%;height:140px;object-fit:cover">';
    };r.readAsDataURL(inp.files[0]);
  }
}
function livePrev(){
  let n=document.querySelector('[name=nama_barang]').value||'Nama Produk';
  let h=parseInt(document.getElementById('harga').value)||0;
  let k=document.querySelector('[name=kategori]').value||'Kategori';
  document.getElementById('prev-name').textContent=n;
  document.getElementById('prev-price').textContent=h?'Rp '+h.toLocaleString('id-ID'):'Rp —';
  document.getElementById('prev-cat').textContent=k;
}
</script>
</body></html>
