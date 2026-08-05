<?php
// session.php - Mulai sesi + cek timeout idle (30 menit)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeoutMinutes = 30;
$timeoutSeconds = $timeoutMinutes * 60;

if (isset($_SESSION['user_id'])) {
    $lastActivity = $_SESSION['last_activity'] ?? 0;
    if ($lastActivity && (time() - $lastActivity) > $timeoutSeconds) {
        // Sesi kedaluwarsa karena idle
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
