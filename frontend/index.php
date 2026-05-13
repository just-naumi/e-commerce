<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>NaumiShop — Belanja Online Terpercaya</title>
<meta name="description" content="Temukan jutaan produk terbaik dengan harga Flash Sale hanya di NaumiShop!">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR LANDING -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">Naumi<em>Shop</em></a>
    <div class="nav-search">
      <input type="text" placeholder="Cari jutaan produk di NaumiShop...">
      <button><i class="fas fa-search"></i></button>
    </div>
    <div class="nav-right">
      <a href="login.php" class="nav-logout"><i class="fas fa-sign-in-alt"></i> Masuk</a>
      <a href="login.php" class="btn-white" style="padding:8px 18px;font-size:13px;border-radius:6px"><i class="fas fa-user-plus"></i> Daftar</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-text fade-up">
      <div class="hero-tag"><i class="fas fa-bolt"></i> Flash Sale Setiap Hari!</div>
      <h1>Belanja Hemat,<br>Kualitas Terbaik!</h1>
      <p>Temukan jutaan produk dari ribuan penjual terpercaya. Harga terjangkau, pengiriman cepat, transaksi aman.</p>
      <div class="hero-btns">
        <a href="login.php"><button class="btn-white"><i class="fas fa-shopping-bag"></i> Mulai Belanja</button></a>
        <a href="login.php"><button class="btn-outline-white"><i class="fas fa-store"></i> Jadi Penjual</button></a>
      </div>
    </div>
    <div class="hero-img fade-up" style="animation-delay:.2s">
      <img src="hero.png" alt="Belanja Online NaumiShop">
    </div>
  </div>
</section>

<!-- STATS BAR -->
<div class="hero-stats">
  <div class="stat-pill"><h3>10K+</h3><p>Produk Terdaftar</p></div>
  <div class="stat-pill"><h3>5K+</h3><p>Penjual Aktif</p></div>
  <div class="stat-pill"><h3>50K+</h3><p>Pembeli Puas</p></div>
  <div class="stat-pill"><h3>99%</h3><p>Transaksi Aman</p></div>
</div>

<!-- FITUR -->
<div style="background:#fff;margin-top:32px;padding:60px 0">
  <div class="section-landing" style="padding-top:0;padding-bottom:0">
    <div class="section-title-center">
      <h2>Kenapa Pilih NaumiShop?</h2>
      <p>Kami hadir dengan layanan terbaik untuk pengalaman belanja online yang menyenangkan</p>
      <div class="underline"></div>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Flash Sale Harian</h3>
        <p>Diskon besar hingga 90% setiap hari. Dapatkan produk impian dengan harga yang tak terduga!</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Transaksi 100% Aman</h3>
        <p>Sistem keamanan berlapis menjaga data dan uang kamu tetap aman di setiap transaksi.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🚀</div>
        <h3>Pengiriman Cepat</h3>
        <p>Tersedia berbagai pilihan jasa pengiriman terpercaya dengan estimasi waktu yang akurat.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🛍️</div>
        <h3>Jutaan Produk</h3>
        <p>Dari fashion, elektronik, makanan, hingga kebutuhan rumah — semua ada di NaumiShop.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💬</div>
        <h3>Dukungan 24/7</h3>
        <p>Tim customer service kami siap membantu kamu kapan saja dan di mana saja.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">↩️</div>
        <h3>Garansi Pengembalian</h3>
        <p>Tidak puas? Kembalikan produk dalam 7 hari, uang kembali penuh tanpa pertanyaan.</p>
      </div>
    </div>
  </div>
</div>

<!-- CARA BELANJA -->
<div class="section-landing">
  <div class="section-title-center">
    <h2>Cara Belanja di NaumiShop</h2>
    <p>Belanja online semudah 3 langkah saja</p>
    <div class="underline"></div>
  </div>
  <div class="steps-grid">
    <div class="step">
      <div class="step-num">1</div>
      <h3>Buat Akun</h3>
      <p>Daftarkan dirimu dengan mudah hanya butuh beberapa detik. Akun kamu langsung aktif!</p>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <h3>Pilih Produk</h3>
      <p>Jelajahi ribuan produk pilihan dengan harga terbaik dan ulasan dari pembeli nyata.</p>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <h3>Bayar & Terima</h3>
      <p>Bayar dengan metode favorit kamu dan produk akan tiba di depan pintu!</p>
    </div>
  </div>
</div>

<!-- CTA SELLER -->
<div style="padding:0 24px;max-width:1300px;margin:0 auto 80px">
  <div class="cta-section">
    <h2>🏪 Mulai Berjualan Sekarang!</h2>
    <p>Bergabung dengan ribuan penjual sukses. Tanpa biaya pendaftaran, langsung bisa jualan!</p>
    <a href="login.php"><button class="btn-white" style="margin:0 auto"><i class="fas fa-store"></i> Daftar sebagai Penjual</button></a>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',e=>{
    e.preventDefault();
    document.querySelector(a.getAttribute('href'))?.scrollIntoView({behavior:'smooth'});
  });
});
// Navbar transparent → solid on scroll
window.addEventListener('scroll',()=>{
  document.querySelector('.navbar').style.boxShadow=window.scrollY>20?'0 4px 20px rgba(0,0,0,.25)':'none';
});
</script>
</body>
</html>
