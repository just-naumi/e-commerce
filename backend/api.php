<?php
// Suppress PHP warnings agar tidak korupsi JSON output
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Buffer output — header tetap bisa diset walau ada output dini

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('DB_HOST') ?: 'database-service';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'passwordtubes';
$db   = getenv('DB_NAME') ?: 'ecommerce_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    ob_clean();
    http_response_code(503);
    die(json_encode(["status"=>"error","message"=>"Database tidak tersedia: ".$conn->connect_error]));
}

// ── AUTO MIGRATION: Tambah kolom baru jika belum ada (tanpa perlu restart DB pod) ──
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS kategori   VARCHAR(50)  NOT NULL DEFAULT 'Lainnya' AFTER foto_barang");
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS deskripsi  TEXT AFTER kategori");
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER penjual_id");

$action = $_GET['action'] ?? '';


// ── 1. LOGIN ──────────────────────────────────────────────
if ($action === 'login') {
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id,username,role FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(["status"=>"success","data"=>$row]);
    } else {
        http_response_code(401);
        echo json_encode(["status"=>"error","message"=>"Username atau password salah"]);
    }
    $stmt->close();
}

// ── 2. GET PRODUCTS (support filter kategori & search) ────
elseif ($action === 'get_products') {
    $kategori = $_GET['kategori'] ?? '';
    $search   = $_GET['search']   ?? '';
    $seller   = intval($_GET['penjual_id'] ?? 0);

    $where = [];
    $params = [];
    $types  = '';

    if ($kategori && $kategori !== 'Semua') {
        $where[]  = 'p.kategori = ?';
        $params[] = $kategori;
        $types   .= 's';
    }
    if ($search) {
        $where[]  = 'p.nama_barang LIKE ?';
        $params[] = "%$search%";
        $types   .= 's';
    }
    if ($seller > 0) {
        $where[]  = 'p.penjual_id = ?';
        $params[] = $seller;
        $types   .= 'i';
    }

    $sql = "SELECT p.*, u.username AS nama_penjual FROM products p
            LEFT JOIN users u ON p.penjual_id = u.id";
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY p.id DESC';

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res  = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    $stmt->close();
}

// ── 3. GET CATEGORIES ────────────────────────────────────
elseif ($action === 'get_categories') {
    $res = $conn->query("SELECT kategori, COUNT(*) as jumlah FROM products GROUP BY kategori ORDER BY jumlah DESC");
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}

// ── 4. GET STATS (for seller dashboard) ──────────────────
elseif ($action === 'get_stats') {
    $penjual_id = intval($_GET['penjual_id'] ?? 0);
    $where = $penjual_id > 0 ? "WHERE penjual_id = $penjual_id" : '';

    $stats = [];

    // Total produk
    $r = $conn->query("SELECT COUNT(*) as total FROM products $where");
    $stats['total_produk'] = $r->fetch_assoc()['total'];

    // Total stok
    $r = $conn->query("SELECT SUM(stok) as total FROM products $where");
    $stats['total_stok'] = $r->fetch_assoc()['total'] ?? 0;

    // Nilai inventori
    $r = $conn->query("SELECT SUM(harga*stok) as total FROM products $where");
    $stats['nilai_inventori'] = $r->fetch_assoc()['total'] ?? 0;

    // Stok hampir habis (1-10)
    $r = $conn->query("SELECT COUNT(*) as total FROM products $where ".($where?'AND':'WHERE')." stok BETWEEN 1 AND 10");
    $stats['hampir_habis'] = $r->fetch_assoc()['total'];

    // Stok habis
    $r = $conn->query("SELECT COUNT(*) as total FROM products $where ".($where?'AND':'WHERE')." stok = 0");
    $stats['stok_habis'] = $r->fetch_assoc()['total'];

    // Per kategori
    $r = $conn->query("SELECT kategori, COUNT(*) as jumlah, SUM(stok) as total_stok FROM products $where GROUP BY kategori ORDER BY jumlah DESC");
    $stats['per_kategori'] = [];
    while ($row = $r->fetch_assoc()) $stats['per_kategori'][] = $row;

    echo json_encode(["status"=>"success","data"=>$stats]);
}

// ── 5. ADD PRODUCT ────────────────────────────────────────
elseif ($action === 'add_product') {
    // Ambil data — TIDAK pakai real_escape_string karena sudah pakai bind_param
    $nama       = trim($_POST['nama_barang'] ?? '');
    $harga      = intval($_POST['harga']     ?? 0);
    $stok       = intval($_POST['stok']      ?? 0);
    $penjual_id = intval($_POST['penjual_id'] ?? 0);
    $kategori   = trim($_POST['kategori']    ?? 'Lainnya');
    $deskripsi  = trim($_POST['deskripsi']   ?? '');

    if (empty($nama) || $harga <= 0 || $penjual_id <= 0) {
        http_response_code(400);
        die(json_encode(["status"=>"error","message"=>"Data tidak lengkap (nama/harga/penjual_id)"]));
    }

    $foto_name = '';
    if (!empty($_FILES['foto']['tmp_name'])) {
        $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            die(json_encode(["status"=>"error","message"=>"Format file tidak didukung: $ext"]));
        }
        $foto_name  = time() . '_' . uniqid() . '.' . $ext;
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_name)) {
            ob_clean(); http_response_code(500);
            die(json_encode(["status"=>"error","message"=>"Gagal upload foto — cek permission folder uploads/"]));
        }
    } else {
        ob_clean(); http_response_code(400);
        die(json_encode(["status"=>"error","message"=>"Foto produk wajib diupload"]));
    }

    $stmt = $conn->prepare(
        "INSERT INTO products (nama_barang,harga,stok,foto_barang,kategori,deskripsi,penjual_id)
         VALUES (?,?,?,?,?,?,?)"
    );
    if (!$stmt) {
        ob_clean(); http_response_code(500);
        die(json_encode(["status"=>"error","message"=>"Prepare gagal: ".$conn->error]));
    }
    // s=string i=int → nama(s) harga(i) stok(i) foto(s) kategori(s) deskripsi(s) penjual_id(i)
    $stmt->bind_param("siisssi", $nama, $harga, $stok, $foto_name, $kategori, $deskripsi, $penjual_id);
    ob_clean();
    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Produk berhasil ditambahkan","id"=>$conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(["status"=>"error","message"=>"Execute gagal: ".$stmt->error]);
    }
    $stmt->close();
}

// ── 6. UPDATE PRODUCT ─────────────────────────────────────
elseif ($action === 'update_product') {
    $id       = intval($_POST['id']    ?? 0);
    $stok     = intval($_POST['stok']  ?? 0);
    $harga    = intval($_POST['harga'] ?? 0);

    if ($id <= 0) { http_response_code(400); die(json_encode(["status"=>"error","message"=>"ID tidak valid"])); }
    $conn->query("UPDATE products SET stok=$stok, harga=$harga WHERE id=$id");
    echo json_encode(["status"=>"success","message"=>"Produk diperbarui"]);
}

// ── 7. DELETE PRODUCT ─────────────────────────────────────
elseif ($action === 'delete_product') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); die(json_encode(["status"=>"error","message"=>"ID tidak valid"])); }

    $res = $conn->query("SELECT foto_barang FROM products WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        $path = __DIR__ . '/uploads/' . $row['foto_barang'];
        if ($row['foto_barang'] && file_exists($path)) unlink($path);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
    echo json_encode(["status"=>"success","message"=>"Produk dihapus"]);
}

// ── DEFAULT ───────────────────────────────────────────────
else {
    http_response_code(404);
    echo json_encode(["status"=>"error","message"=>"Action tidak dikenal: $action"]);
}

$conn->close();
?>