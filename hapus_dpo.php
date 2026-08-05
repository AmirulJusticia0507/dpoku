<?php
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Ambil foto untuk dihapus dari disk
$stmt = $koneksidpogendeng->prepare("SELECT foto FROM dpo WHERE id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

$del = $koneksidpogendeng->prepare("DELETE FROM dpo WHERE id = ?");
if ($del->execute([$id]) && $del->rowCount() > 0) {
    // Hapus foto dari disk (termasuk versi asli non-frame)
    if ($d && !empty($d['foto']) && strpos($d['foto'], 'uploads/') === 0) {
        if (file_exists($d['foto'])) unlink($d['foto']);
        // Asli non-frame: uploads/xxx.jpg saat foto = uploads/framed_xxx.jpg
        $orig = 'uploads/' . ltrim(str_replace('framed_', '', basename($d['foto'])), '/');
        if ($orig !== $d['foto'] && file_exists($orig)) unlink($orig);
    }
    // Hapus file barang bukti dari disk (rekaman DB sudah cascade)
    $bk = $koneksidpogendeng->prepare("SELECT nama_file FROM barang_bukti WHERE dpo_id = ?");
    $bk->execute([$id]);
    foreach ($bk->fetchAll() as $row) {
        $p = 'uploads/bukti/' . $row['nama_file'];
        if (file_exists($p)) unlink($p);
    }
    include __DIR__.'/lib/audit_log.php';
    log_audit('delete', 'dpo', $id, "Hapus DPO #$id");
    echo "<script>alert('Data DPO berhasil dihapus!'); window.location.href='index.php';</script>";
} else {
    echo "<script>alert('Data DPO tidak ditemukan.'); window.location.href='index.php';</script>";
}
?>
