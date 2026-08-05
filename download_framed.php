<?php
// download_framed.php - Unduh foto DPO yang sudah di-frame "WANTED"
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('ID tidak valid.');
}

$stmt = $koneksidpogendeng->prepare("SELECT foto, nama_lengkap FROM dpo WHERE id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d || empty($d['foto']) || !file_exists($d['foto'])) {
    http_response_code(404);
    exit('Foto tidak ditemukan.');
}

include __DIR__.'/lib/audit_log.php';
log_audit('export', 'dpo', $id, "Download foto framed DPO #$id");

$fname = 'framed_' . preg_replace('/[^A-Za-z0-9 ]/', '', $d['nama_lengkap']) . '.jpg';
header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="' . $fname . '"');
readfile($d['foto']);
exit();
?>
