<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Membaca dari ENV (Sesuai file .env)
$host = getenv('DB_HOST') ?: 'database-service';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'passwordtubes';
$db   = getenv('DB_NAME') ?: 'ecommerce_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die(json_encode(["status" => "error", "message" => "Database down!"]));

$action = $_GET['action'] ?? '';

// 1. Auth Login
if ($action == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $res = $conn->query("SELECT id, username, role FROM users WHERE username='$username' AND password='$password'");
    if ($row = $res->fetch_assoc()) {
        echo json_encode(["status" => "success", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "message" => "Login gagal"]);
    }
}

// 2. Read Products
elseif ($action == 'get_products') {
    $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
}

// 3. Create Product
elseif ($action == 'add_product') {
    $nama = $_POST['nama_barang'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $penjual_id = $_POST['penjual_id'];
    
    $foto_name = time() . "_" . $_FILES['foto']['name'];
    move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $foto_name);
    
    $conn->query("INSERT INTO products (nama_barang, harga, stok, foto_barang, penjual_id) VALUES ('$nama', $harga, $stok, '$foto_name', $penjual_id)");
    echo json_encode(["status" => "success"]);
}

// 4. Delete Product
elseif ($action == 'delete_product') {
    $id = $_POST['id'];
    $res = $conn->query("SELECT foto_barang FROM products WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if(file_exists("uploads/" . $row['foto_barang'])) unlink("uploads/" . $row['foto_barang']);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
    echo json_encode(["status" => "success"]);
}
?>