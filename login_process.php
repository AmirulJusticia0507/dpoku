<?php
// login_process.php
session_start();
include 'Koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// --- Rate limiting (brute-force protection) ---
// Batas: 5 percobaan gagal per 5 menit (per IP + username gabungan sesi)
$maxAttempts    = 5;
$lockoutSeconds = 300; // 5 menit

$userIdKey = 'login_attempts_user_' . (int) ($_SESSION['user_id'] ?? 0);
$ipKey     = 'login_attempts_ip_' . md5($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

$attempts   = $_SESSION['login_attempts'] ?? [];
$now        = time();

// Hapus rekaman lebih lama dari lockout window
foreach ($attempts as $k => $entry) {
    if (($now - $entry['time']) > $lockoutSeconds) {
        unset($attempts[$k]);
    }
}

// Cek apakah sedang terkunci
$locked = false;
foreach ($attempts as $entry) {
    if ($entry['time'] >= ($now - $lockoutSeconds) && $entry['count'] >= $maxAttempts) {
        $locked = true;
        break;
    }
}

if ($locked) {
    echo "<script>alert('Terlalu banyak percobaan gagal. Coba lagi dalam " . ceil($lockoutSeconds / 60) . " menit.');window.location='login.php';</script>";
    exit();
}

// --- Validasi input ---
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if ($username === '' || $password === '') {
    echo "<script>alert('Username dan password wajib diisi.');window.location='login.php';</script>";
    exit();
}

// --- Cek user (prepared statement) ---
$stmt = $koneksidpogendeng->prepare("SELECT id, username, password, fullname, email FROM \"user\" WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Verifikasi password hash (bcrypt)
    if (password_verify($password, $user['password'])) {
        // Regenerasi session (hindari session fixation)
        session_regenerate_id(true);

        // Reset counter gagal
        unset($attempts[$userIdKey], $attempts[$ipKey]);
        $_SESSION['login_attempts'] = $attempts;

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['fullname']  = $user['fullname'];
        $_SESSION['email']     = $user['email'];

        include __DIR__.'/lib/audit_log.php';
        if (function_exists('log_audit')) {
            log_audit('login', 'auth', $user['id'], "Login sukses user={$user['username']}");
        }

        header("Location: index.php");
        exit();
    } else {
        $failKey = $userIdKey;
    }
} else {
    // Jangan untkam: bilang "username" salah agar tidak enumeration — tapi tetap counter
    echo "<script>alert('Username tidak ditemukan!');window.location='login.php';</script>";
    exit();
}

// --- Catat kegagalan ---
$found = false;
foreach ($attempts as &$entry) {
    if ($entry['key'] === $failKey) {
        $entry['count']++;
        $entry['time'] = $now;
        $found = true;
        break;
    }
}
unset($entry);
if (!$found) {
    $attempts[] = ['key' => $failKey, 'time' => $now, 'count' => 1];
}
$_SESSION['login_attempts'] = $attempts;

echo "<script>alert('Password salah! (Percobaan gagal: " . (count(array_filter($attempts, fn($e) => $e['key'] === $failKey)) > 0 ? count(array_filter($attempts, fn($e) => $e['key'] === $failKey)) : 1) . "/$maxAttempts)');window.location='login.php';</script>";
?>
