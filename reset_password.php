<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['reset_username'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $username = $_SESSION['reset_username'];

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        mysqli_query($koneksidpogendeng, "UPDATE user SET password='$hashed_password' WHERE username='$username'");
        unset($_SESSION['reset_username']);
        echo "<script>alert('Password berhasil direset. Silakan login kembali'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Password tidak sama!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center">Reset Password</h3>
    <form method="POST">
        <div class="form-group">
            <label>Password Baru</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success btn-block">Reset Password</button>
    </form>
</div>
</body>
</html>
