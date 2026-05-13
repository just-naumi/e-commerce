<?php
// Pengaturan koneksi ke database (Nanti akan diarahkan ke service K8s)
$host = getenv('DB_HOST') ?: 'database-service';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'passwordtubes';
$db   = getenv('DB_NAME') ?: 'flashsale_db';

// Mencoba koneksi
$conn = new mysqli($host, $user, $pass, $db);
$status = ($conn->connect_error) ? "Gagal Terhubung: " . $conn->connect_error : "Terkoneksi dengan Database";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flash Sale Tubes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <h1 class="display-4 text-danger fw-bold">🔥 FLASH SALE 11.11 🔥</h1>
        <p class="lead">Simulasi Auto-scaling Infrastruktur Cloud</p>
        
        <div class="card mx-auto mt-4" style="max-width: 400px;">
            <div class="card-body">
                <h5 class="card-title">Sepatu Kets Awam</h5>
                <p class="card-text">Harga Normal: <s>Rp 500.000</s></p>
                <h3 class="text-success">Rp 99.000</h3>
                <button class="btn btn-danger btn-lg w-100 mt-3">Beli Sekarang!</button>
            </div>
        </div>

        <div class="mt-5 p-3 bg-dark text-white rounded">
            <strong>Status Sistem:</strong> <?php echo $status; ?>
        </div>
    </div>
</body>
</html>