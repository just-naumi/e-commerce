<?php
session_start();
$backend_url = getenv('BACKEND_URL') ?: 'http://X.X.X.X:30081/api.php';

if (isset($_POST['login'])) {
    $ch = curl_init("$backend_url?action=login");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if ($response['status'] == 'success') {
        $_SESSION['user'] = $response['data'];
        if ($_SESSION['user']['role'] == 'penjual') header("Location: penjual.php");
        else header("Location: pembeli.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NaumiShop</title>
    <meta name="description" content="Login ke NaumiShop dan nikmati Flash Sale terbaik!">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">
    <!-- Kiri: Branding -->
    <div class="login-left">
        <div class="login-illustration">🛍️</div>
        <h1>NaumiShop</h1>
        <p>Platform belanja online terpercaya.<br>Temukan jutaan produk dengan harga terbaik<br>langsung dari penjual terpercaya.</p>
    </div>

    <!-- Kanan: Form Login -->
    <div class="login-right fade-up">
        <h2>Masuk ke Akun</h2>
        <p class="sub">Selamat datang kembali di NaumiShop!</p>

        <?php if (isset($error)): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <form method="POST" style="width:100%">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-user" style="color:var(--primary);margin-right:6px"></i>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-lock" style="color:var(--primary);margin-right:6px"></i>Password</label>
                <input type="password" name="password" id="pwd" class="form-control" placeholder="Masukkan password" required>
            </div>
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:20px">
                <input type="checkbox" id="show-pwd" onchange="document.getElementById('pwd').type=this.checked?'text':'password'">
                <label for="show-pwd" style="font-size:13px;color:var(--gray);cursor:pointer">Tampilkan password</label>
            </div>
            <button type="submit" name="login" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="demo-info">
            <p>🏪 <strong>Akun Penjual:</strong> Naufal / password123</p>
            <p>🛒 <strong>Akun Pembeli:</strong> Ruth / password123</p>
        </div>
    </div>
</div>
</body>
</html>