<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') { header("Location: login.php"); exit; }
$backend_url = getenv('BACKEND_URL') ?: 'http://backend-service/api.php';
$base_url = str_replace("/api.php", "", $backend_url);

if (isset($_GET['hapus'])) {
    $ch = curl_init("$backend_url?action=delete_product");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["id" => $_GET['hapus']]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch); curl_close($ch);
    header("Location: penjual.php");
}

if (isset($_POST['tambah'])) {
    $cfile = new CURLFile($_FILES['foto']['tmp_name'], $_FILES['foto']['type'], $_FILES['foto']['name']);
    $postData = ["nama_barang" => $_POST['nama_barang'], "harga" => $_POST['harga'], "stok" => $_POST['stok'], "penjual_id" => $_SESSION['user']['id'], "foto" => $cfile];
    $ch = curl_init("$backend_url?action=add_product");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch); curl_close($ch);
    header("Location: penjual.php");
}

$products = json_decode(file_get_contents("$backend_url?action=get_products"), true);
?>
<!DOCTYPE html>
<html lang="id">
<head><title>Dashboard Penjual</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light pb-5">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">🏪 Toko: <?= $_SESSION['user']['username'] ?></span>
            <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Tambah Produk</div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="text" name="nama_barang" class="form-control mb-2" placeholder="Nama Barang" required>
                            <input type="number" name="harga" class="form-control mb-2" placeholder="Harga" required>
                            <input type="number" name="stok" class="form-control mb-2" placeholder="Stok" required>
                            <input type="file" name="foto" class="form-control mb-3" required accept="image/*">
                            <button type="submit" name="tambah" class="btn btn-primary w-100">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">Kelola Produk</div>
                    <div class="card-body">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark"><tr><th>Foto</th><th>Barang</th><th>Harga/Stok</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php if($products): foreach($products as $p): ?>
                                <tr>
                                    <td><img src="<?= $base_url ?>/uploads/<?= $p['foto_barang'] ?>" width="60" class="rounded"></td>
                                    <td class="fw-bold"><?= $p['nama_barang'] ?></td>
                                    <td>Rp <?= number_format($p['harga']) ?><br><span class="badge bg-secondary"><?= $p['stok'] ?> Pcs</span></td>
                                    <td><a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Hapus</a></td>
                                </tr>
                                <?php endforeach; else: echo "<tr><td colspan='4'>Belum ada produk</td></tr>"; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>