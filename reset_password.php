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

    if ($new_password === $confirm_password && strlen($new_password) >= 8) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // Prepared statement (hindari SQL injection)
        $stmt = $koneksidpogendeng->prepare("UPDATE user SET password = ? WHERE username = ?");
        $stmt->bind_param('ss', $hashed_password, $username);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['reset_username']);
        include __DIR__.'/lib/audit_log.php';
        log_audit('update', 'user', null, "Reset password user $username via forgot");
        echo "<script>alert('Password berhasil direset. Silakan login kembali'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Password minimal 8 karakter dan tidak sama!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 rounded-2xl shadow-2xl p-8">
            <h3 class="text-center text-white text-xl font-bold mb-6">Reset Password</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Password Baru</label>
                    <input type="password" name="new_password" required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
