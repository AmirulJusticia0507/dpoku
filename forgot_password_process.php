<?php
session_start();
include 'koneksi.php';
include __DIR__.'/lib/audit_log.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        echo "<script>alert('Username wajib diisi.');window.location='forgot_password.php';</script>";
        exit();
    }

    // Prepared statement (hindari SQL injection)
    $stmt = $koneksidpogendeng->prepare("SELECT id, username FROM user WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $_SESSION['reset_username'] = $username;
        log_audit('forgot_password', 'auth', null, "User lupa password: $username");
        header("Location: reset_password.php");
        exit();
    } else {
        log_audit('forgot_password_failed', 'auth', null, "Lupa password gagal username=$username");
        echo "<script>alert('Username tidak ditemukan!');window.location='forgot_password.php';</script>";
    }
}
?>
