<?php
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$dpo_id = (int) ($_POST['dpo_id'] ?? 0);
$keterangan = trim($_POST['keterangan'] ?? '');
$userId = (int) $_SESSION['user_id'];

if ($dpo_id <= 0) {
    die("Invalid DPO ID.");
}

// Pastikan DPO benar-benar ada (hindari file yatim + FK violation)
$cekDpo = $koneksidpogendeng->prepare("SELECT id FROM dpo WHERE id = ?");
$cekDpo->execute([$dpo_id]);
if (!$cekDpo->fetch()) {
    die("DPO tidak ditemukan.");
}

// Pastikan folder ada
$dir = 'uploads/bukti/';
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

$allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'mp4', 'avi', 'mkv', 'mp3', 'wav', 'm4a'];
$uploaded = 0;

if (!empty($_FILES['bukti']['name'])) {
    $files = $_FILES['bukti'];
    $total = count($files['name']);

    for ($i = 0; $i < $total; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;

        $size = (int) $files['size'][$i];
        if ($size > 20 * 1024 * 1024) continue; // max 20 MB per file

        $fname = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        if (move_uploaded_file($files['tmp_name'][$i], $dir . $fname)) {
            $stmt = $koneksidpogendeng->prepare(
                "INSERT INTO barang_bukti (dpo_id, nama_file, tipe_file, ukuran, keterangan, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$dpo_id, $fname, $files['type'][$i], $size, $keterangan, $userId]);
                $uploaded++;
            } catch (PDOException $e) {
                // Gagal insert -> bersihkan file yang sudah pindah
                @unlink($dir . $fname);
            }
        }
    }
}

if ($uploaded > 0) {
    include __DIR__.'/lib/audit_log.php';
    log_audit('create', 'barang_bukti', $dpo_id, "Upload $uploaded barang bukti untuk DPO #$dpo_id");
    echo "<script>alert('$uploaded file berhasil diupload!'); window.location.href='detail_dpo.php?id=$dpo_id';</script>";
} else {
    echo "<script>alert('Tidak ada file valid yang diupload.'); window.location.href='detail_dpo.php?id=$dpo_id';</script>";
}
?>
