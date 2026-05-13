<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'penjual') {
    header("Location: login.php"); exit;
}
$backend_url = getenv('BACKEND_URL') ?: 'http://X.X.X.X:30081/api.php';
$base_url = str_replace("/api.php", "", $backend_url);

// Hapus produk
if (isset($_GET['hapus'])) {
    $ch = curl_init("$backend_url?action=delete_product");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["id" => intval($_GET['hapus'])]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch); curl_close($ch);
    header("Location: penjual.php?deleted=1"); exit;
}

// Tambah produk
if (isset($_POST['tambah'])) {
    $cfile = new CURLFile($_FILES['foto']['tmp_name'], $_FILES['foto']['type'], $_FILES['foto']['name']);
    $postData = [
        "nama_barang" => $_POST['nama_barang'],
        "harga"       => $_POST['harga'],
        "stok"        => $_POST['stok'],
        "penjual_id"  => $_SESSION['user']['id'],
        "foto"        => $cfile
    ];
    $ch = curl_init("$backend_url?action=add_product");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch); curl_close($ch);
    header("Location: penjual.php?added=1"); exit;
}

$products = json_decode(file_get_contents("$backend_url?action=get_products"), true) ?: [];
$username = $_SESSION['user']['username'];
$initial  = strtoupper(substr($username, 0, 1));
$total_stok = array_sum(array_column($products, 'stok'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual — NaumiShop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #F5F5F5; }
        .top-bar {
            background: var(--orange-gradient);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .top-bar-logo { color: white; font-size: 18px; font-weight: 700; }
        .top-bar-logo span { opacity: 0.75; font-weight: 300; }
        .top-bar-right { display: flex; align-items: center; gap: 12px; }
        .top-bar-user { display: flex; align-items: center; gap: 8px; color: white; font-size: 13px; }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="top-bar-logo">Naumi<span>Shop</span> &nbsp;<span style="font-size:12px;background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:12px;font-weight:400">Seller Center</span></div>
    <div class="top-bar-right">
        <div class="top-bar-user">
            <div class="avatar"><?= $initial ?></div>
            <span><?= htmlspecialchars($username) ?></span>
        </div>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</div>

<div class="seller-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h3><i class="fas fa-store" style="margin-right:6px"></i><?= htmlspecialchars($username) ?></h3>
            <p>Seller Account</p>
        </div>
        <nav class="sidebar-nav">
            <a href="penjual.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="#form-produk"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
            <a href="#list-produk"><i class="fas fa-box"></i> Produk Saya</a>
            <a href="pembeli.php" target="_blank"><i class="fas fa-eye"></i> Lihat Toko</a>
            <a href="logout.php" style="margin-top:auto;color:#EF4444"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="seller-content">

        <!-- Greeting -->
        <div style="margin-bottom:20px">
            <h2 style="font-size:20px;font-weight:700">Selamat datang, <?= htmlspecialchars($username) ?>! 👋</h2>
            <p style="color:var(--gray);font-size:13px">Kelola produk toko kamu dari sini.</p>
        </div>

        <!-- Toast notif dari redirect -->
        <?php if (isset($_GET['added'])): ?>
        <div class="toast show" id="toast" style="position:static;margin-bottom:16px;transform:none;opacity:1;background:#065F46;border-radius:8px">
            ✅ Produk berhasil ditambahkan!
        </div>
        <?php elseif (isset($_GET['deleted'])): ?>
        <div class="toast show" id="toast" style="position:static;margin-bottom:16px;transform:none;opacity:1;background:#991B1B;border-radius:8px">
            🗑️ Produk berhasil dihapus.
        </div>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <h3><?= count($products) ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-cubes"></i></div>
                <div class="stat-info">
                    <h3><?= number_format($total_stok) ?></h3>
                    <p>Total Stok</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-store"></i></div>
                <div class="stat-info">
                    <h3>Aktif</h3>
                    <p>Status Toko</p>
                </div>
            </div>
        </div>

        <!-- FORM TAMBAH PRODUK -->
        <div class="card" id="form-produk" style="margin-bottom:24px">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Tambah Produk Baru</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="nama_barang" class="form-control" placeholder="cth. Sepatu Sneakers Putih" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control" placeholder="cth. 150000" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" placeholder="cth. 50" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Foto Produk</label>
                            <div class="upload-area" id="upload-area">
                                <input type="file" name="foto" accept="image/*" required id="foto-input" onchange="previewFoto(this)">
                                <img id="preview-img" src="" alt="preview">
                                <i class="fas fa-cloud-upload-alt" id="upload-icon"></i>
                                <p id="upload-text">Klik atau seret foto ke sini</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="tambah" class="btn-primary" style="max-width:200px">
                        <i class="fas fa-plus"></i> Simpan Produk
                    </button>
                </form>
            </div>
        </div>

        <!-- LIST PRODUK -->
        <div class="card" id="list-produk">
            <div class="card-header">
                <h3><i class="fas fa-box"></i> Daftar Produk Saya</h3>
                <span style="font-size:13px;color:var(--gray)"><?= count($products) ?> produk</span>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($products && count($products) > 0): ?>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['foto_barang'])): ?>
                                        <img src="<?= $base_url ?>/uploads/<?= htmlspecialchars($p['foto_barang']) ?>"
                                             class="thumb" alt=""
                                             onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div class="thumb-placeholder"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight:500"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                <td><?= $p['stok'] ?> pcs</td>
                                <td>
                                    <?php if ($p['stok'] > 20): ?>
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Tersedia</span>
                                    <?php elseif ($p['stok'] > 0): ?>
                                        <span class="badge badge-warn"><i class="fas fa-exclamation-circle"></i> Hampir Habis</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?hapus=<?= $p['id'] ?>"
                                       onclick="return confirm('Hapus produk <?= htmlspecialchars($p['nama_barang']) ?>?')"
                                       class="btn-del">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>Belum ada produk. Tambahkan produk pertama kamu!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- TOAST (floating) -->
<div class="toast" id="toast-float"></div>

<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('preview-img');
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById('upload-icon').style.display = 'none';
            document.getElementById('upload-text').style.display = 'none';
            document.getElementById('upload-area').classList.add('has-file');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto-hide flash message
setTimeout(() => {
    let el = document.getElementById('toast');
    if (el) el.style.opacity = '0';
}, 3500);
</script>
</body>
</html>