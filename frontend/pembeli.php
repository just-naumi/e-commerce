<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') {
    header("Location: login.php"); exit;
}
$backend_url = getenv('BACKEND_URL') ?: 'http://X.X.X.X:30081/api.php';
$base_url = str_replace("/api.php", "", $backend_url);

$products = json_decode(file_get_contents("$backend_url?action=get_products"), true);
$username = $_SESSION['user']['username'];
$initial  = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaumiShop — Flash Sale 🔥</title>
    <meta name="description" content="Flash Sale terbaik hanya di NaumiShop. Belanja hemat, kualitas terjamin!">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-top">
        <div class="navbar-logo">Naumi<span>Shop</span></div>
        <div class="search-bar">
            <input type="text" placeholder="Cari produk di NaumiShop..." id="search-input">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="navbar-actions">
            <a href="#"><i class="fas fa-shopping-cart"></i>Keranjang</a>
            <div class="navbar-user">
                <div class="avatar"><?= $initial ?></div>
                <span style="font-size:13px"><?= htmlspecialchars($username) ?></span>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>
</nav>

<!-- HERO FLASH SALE BANNER -->
<div class="hero-banner">
    <h1>⚡ FLASH SALE</h1>
    <p>Penawaran terbatas! Jangan sampai kehabisan.</p>
    <div class="countdown">
        <div class="countdown-box" id="h">00</div>
        <span class="countdown-sep">:</span>
        <div class="countdown-box" id="m">00</div>
        <span class="countdown-sep">:</span>
        <div class="countdown-box" id="s">00</div>
    </div>
</div>

<!-- PRODUK SECTION -->
<div class="section">
    <div class="section-header">
        <div class="section-title">
            <i class="fas fa-fire"></i> Produk Flash Sale
        </div>
        <span class="see-all"><?= $products ? count($products) . ' produk' : '0 produk' ?></span>
    </div>

    <div class="product-grid" id="product-grid">
        <?php if ($products && count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
            <div class="product-card fade-up" data-name="<?= strtolower(htmlspecialchars($p['nama_barang'])) ?>">
                <div class="flash-badge">FLASH SALE</div>
                <?php if (!empty($p['foto_barang'])): ?>
                    <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($p['foto_barang']) ?>"
                         class="product-card-img" alt="<?= htmlspecialchars($p['nama_barang']) ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="product-card-img-placeholder" style="display:none"><i class="fas fa-image"></i></div>
                <?php else: ?>
                    <div class="product-card-img-placeholder"><i class="fas fa-image"></i></div>
                <?php endif; ?>
                <div class="product-card-body">
                    <div class="product-name"><?= htmlspecialchars($p['nama_barang']) ?></div>
                    <div class="product-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                    <div class="product-stock">Sisa <?= $p['stok'] ?> pcs</div>
                    <?php
                        $pct = min(100, ($p['stok'] / 100) * 100);
                        $pct = max(5, $pct);
                    ?>
                    <div class="stock-bar"><div class="stock-fill" style="width:<?= $pct ?>%"></div></div>
                    <button class="btn-buy" onclick="addToCart('<?= htmlspecialchars($p['nama_barang']) ?>')">
                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fas fa-box-open"></i>
                <p>Belum ada produk tersedia</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
// Countdown Timer
function startCountdown() {
    let end = new Date();
    end.setHours(23, 59, 59, 0);
    setInterval(() => {
        let now  = new Date();
        let diff = Math.max(0, Math.floor((end - now) / 1000));
        let h = String(Math.floor(diff / 3600)).padStart(2,'0');
        let m = String(Math.floor((diff % 3600) / 60)).padStart(2,'0');
        let s = String(diff % 60).padStart(2,'0');
        document.getElementById('h').textContent = h;
        document.getElementById('m').textContent = m;
        document.getElementById('s').textContent = s;
    }, 1000);
}
startCountdown();

// Search Filter
document.getElementById('search-input').addEventListener('input', function() {
    let q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? 'block' : 'none';
    });
});

// Toast
function showToast(msg) {
    let t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}

function addToCart(name) {
    showToast('✅ ' + name + ' ditambahkan ke keranjang!');
}
</script>
</body>
</html>