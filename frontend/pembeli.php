<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'pembeli') { header("Location: login.php"); exit; }
$backend_url = getenv('BACKEND_URL') ?: 'http://backend-service/api.php';
$base_url = str_replace("/api.php", "", $backend_url);

$products = json_decode(file_get_contents("$backend_url?action=get_products"), true);
?>
<!DOCTYPE html>
<html lang="id">
<head><title>Dashboard Pembeli</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-danger mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">🛒 Halo, <?= $_SESSION['user']['username'] ?>!</span>
            <a href="logout.php" class="btn btn-sm btn-light text-danger fw-bold">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h3 class="mb-4">Flash Sale Sedang Berlangsung! 🔥</h3>
        <div class="row">
            <?php if($products): foreach($products as $p): ?>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="<?= $base_url ?>/uploads/<?= $p['foto_barang'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold"><?= $p['nama_barang'] ?></h5>
                        <p class="text-success fs-5 mb-1 fw-bold">Rp <?= number_format($p['harga']) ?></p>
                        <p class="text-muted small">Sisa Stok: <?= $p['stok'] ?> Pcs</p>
                        <button class="btn btn-danger btn-sm w-100 fw-bold">Masukkan Keranjang</button>
                    </div>
                </div>
            </div>
            <?php endforeach; else: echo "<p>Barang habis diborong!</p>"; endif; ?>
        </div>
    </div>
</body>
</html>