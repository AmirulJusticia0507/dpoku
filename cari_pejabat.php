<?php
// cari_pejabat.php - autocomplete nama dari daftar_pejabat (untuk integrasi form DPO)
include 'session.php';
include 'Koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
$q = substr($q, 0, 100);
if ($q === '') {
    echo json_encode([]);
    exit;
}

$stmt = $koneksidpogendeng->prepare(
    "SELECT nama, jabatan, instansi, sumber
     FROM daftar_pejabat
     WHERE nama ILIKE ?
     ORDER BY sumber, nama
     LIMIT 10"
);
$stmt->execute(["%$q%"]);
echo json_encode($stmt->fetchAll());
