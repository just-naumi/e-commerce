<?php
session_start();
$backend_url = getenv('BACKEND_URL') ?: 'http://backend-service/api.php';

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
<head><title>Login - Flash Sale</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container text-center" style="max-width: 400px;">
        <h2 class="mb-4 text-danger fw-bold">🔥 Login E-Commerce</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="card shadow-sm p-4">
            <form method="POST">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                <button type="submit" name="login" class="btn btn-danger w-100">Masuk</button>
            </form>
        </div>
        <p class="mt-3 text-muted">Akses Penjual: <b>toko_naumi</b><br>Akses Pembeli: <b>buyer_mpay1</b><br>Pass: password123</p>
    </div>
</body>
</html><?php
session_start();
$backend_url = getenv('BACKEND_URL') ?: 'http://backend-service/api.php';

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
<head><title>Login - Flash Sale</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container text-center" style="max-width: 400px;">
        <h2 class="mb-4 text-danger fw-bold">🔥 Login E-Commerce</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="card shadow-sm p-4">
            <form method="POST">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                <button type="submit" name="login" class="btn btn-danger w-100">Masuk</button>
            </form>
        </div>
        <p class="mt-3 text-muted">Akses Penjual: <b>toko_naumi</b><br>Akses Pembeli: <b>buyer_mpay1</b><br>Pass: password123</p>
    </div>
</body>
</html>