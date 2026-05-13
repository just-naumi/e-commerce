<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Koneksi DB via ENV (di-set dari Kubernetes ConfigMap / Secret)
$host = getenv('DB_HOST') ?: 'database-service';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'passwordtubes';
$db   = getenv('DB_NAME') ?: 'ecommerce_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(503);
    die(json_encode(["status" => "error", "message" => "Database tidak tersedia"]));
}

$action = $_GET['action'] ?? '';

// ── 1. LOGIN ──────────────────────────────────────────────────────────────
if ($action === 'login') {
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["status" => "success", "data" => $row]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Username atau password salah"]);
    }
    $stmt->close();
}

// ── 2. GET PRODUCTS ───────────────────────────────────────────────────────
elseif ($action === 'get_products') {
    $res  = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// ── 3. ADD PRODUCT ────────────────────────────────────────────────────────
elseif ($action === 'add_product') {
    $nama       = $conn->real_escape_string($_POST['nama_barang'] ?? '');
    $harga      = intval($_POST['harga']      ?? 0);
    $stok       = intval($_POST['stok']       ?? 0);
    $penjual_id = intval($_POST['penjual_id'] ?? 0);

    // Validasi sederhana
    if (empty($nama) || $harga <= 0 || $stok < 0 || $penjual_id <= 0) {
        http_response_code(400);
        die(json_encode(["status" => "error", "message" => "Data tidak lengkap atau tidak valid"]));
    }

    // Upload foto
    if (empty($_FILES['foto']['tmp_name'])) {
        http_response_code(400);
        die(json_encode(["status" => "error", "message" => "Foto produk wajib diupload"]));
    }

    $ext       = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto_name = time() . '_' . uniqid() . '.' . $ext;
    $upload_dir = __DIR__ . '/uploads/';

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_name)) {
        http_response_code(500);
        die(json_encode(["status" => "error", "message" => "Gagal menyimpan foto"]));
    }

    $stmt = $conn->prepare("INSERT INTO products (nama_barang, harga, stok, foto_barang, penjual_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisi", $nama, $harga, $stok, $foto_name, $penjual_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Produk berhasil ditambahkan"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database"]);
    }
    $stmt->close();
}

// ── 4. DELETE PRODUCT ─────────────────────────────────────────────────────
elseif ($action === 'delete_product') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        die(json_encode(["status" => "error", "message" => "ID tidak valid"]));
    }

    // Hapus file foto
    $res = $conn->query("SELECT foto_barang FROM products WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        $foto_path = __DIR__ . '/uploads/' . $row['foto_barang'];
        if (file_exists($foto_path)) unlink($foto_path);
    }

    $conn->query("DELETE FROM products WHERE id = $id");
    echo json_encode(["status" => "success", "message" => "Produk berhasil dihapus"]);
}

// ── DEFAULT ───────────────────────────────────────────────────────────────
else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Action tidak dikenal: $action"]);
}

$conn->close();
?>