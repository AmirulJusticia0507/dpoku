<?php
// login_process.php
session_start();
include 'Koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$maxAttempts    = 5;
$lockoutSeconds = 300; // 5 menit

// --- Validasi input ---
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    echo "<script>alert('Username dan password wajib diisi.');window.location='login.php';</script>";
    exit();
}

// --- Ambil user (prepared statement) ---
$stmt = $koneksidpogendeng->prepare(
    "SELECT id, username, password, fullname, email, role, failed_attempts, locked_until
     FROM \"user\" WHERE username = ?"
);
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<script>alert('Username tidak ditemukan!');window.location='login.php';</script>";
    exit();
}

// --- Cek lockout dari DB ---
$lockedUntil = $user['locked_until'];
if ($lockedUntil && strtotime($lockedUntil) > time()) {
    $mins = ceil((strtotime($lockedUntil) - time()) / 60);
    echo "<script>alert('Akun dikunci karena terlalu banyak percobaan gagal. Coba lagi dalam {$mins} menit.');window.location='login.php';</script>";
    exit();
}

// --- Verifikasi password ---
if (password_verify($password, $user['password'])) {
    // Reset counter & lockout
    $koneksidpogendeng->prepare('UPDATE "user" SET failed_attempts = 0, locked_until = NULL WHERE id = ?')
        ->execute([$user['id']]);

    // Regenerasi session (hindari session fixation)
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['fullname']  = $user['fullname'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'] ?: 'operator';
    $_SESSION['last_activity'] = time();

    include __DIR__.'/lib/audit_log.php';
    if (function_exists('log_audit')) {
        log_audit('login', 'auth', $user['id'], "Login sukses user={$user['username']}");
    }

    header("Location: index.php");
    exit();
}

// --- Password salah: catat kegagalan di DB ---
$newCount = (int) $user['failed_attempts'] + 1;
if ($newCount >= $maxAttempts) {
    $koneksidpogendeng->prepare(
        'UPDATE "user" SET failed_attempts = ?, locked_until = NOW() + (? || \' seconds\')::interval WHERE id = ?'
    )->execute([$newCount, $lockoutSeconds, $user['id']]);
} else {
    $koneksidpogendeng->prepare('UPDATE "user" SET failed_attempts = ? WHERE id = ?')
        ->execute([$newCount, $user['id']]);
}

include __DIR__.'/lib/audit_log.php';
if (function_exists('log_audit')) {
    log_audit('login_failed', 'auth', $user['id'], "Password salah ({$newCount}/{$maxAttempts}) user={$username}");
}

$sisa = $maxAttempts - $newCount;
echo "<script>alert('Password salah! Kesempatan tersisa: {$sisa}.');window.location='login.php';</script>";
exit();
?>
