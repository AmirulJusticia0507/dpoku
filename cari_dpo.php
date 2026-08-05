<?php
include 'Koneksi.php';

$nik   = trim($_GET['nik'] ?? '');
$nama  = trim($_GET['nama'] ?? '');
$inst  = trim($_GET['instansi'] ?? '');

// Batasi panjang input (hindari query berbelit)
$nik   = substr($nik, 0, 50);
$nama  = substr($nama, 0, 100);
$inst  = substr($inst, 0, 100);

$where = [];
$params = [];

if ($nik !== '')  { $where[] = "nik = ?";             $params[] = $nik; }
if ($nama !== '') { $where[] = "nama_lengkap ILIKE ?"; $params[] = "%$nama%"; }
if ($inst !== '') { $where[] = "nama_instansi = ?";   $params[] = $inst; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Prepared statement (hindari SQL injection)
$sql = "SELECT dpo.nik, dpo.nama_lengkap, dpo.tanggal_lahir, dpo.jenis_kelamin,
               dpo.nama_instansi, dpo.jenis_kasus, dpo.jenis_hukuman, dpo.status_dpo,
               COALESCE(bounty.jumlah_bounty, 0) AS jumlah_bounty,
               dpo.foto
        FROM dpo
        LEFT JOIN bounty ON bounty.id_kasus = CASE WHEN dpo.jenis_kasus ~ '^[0-9]+$' THEN dpo.jenis_kasus::int ELSE NULL END
        $whereSql
        ORDER BY dpo.created_at DESC
        LIMIT 1";

$stmt = $koneksidpogendeng->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed']);
    exit();
}

$stmt->execute($params);
$row = $stmt->fetch();

if ($row) {
    echo json_encode([
        'status'      => 'success',
        'nama_lengkap'  => $row['nama_lengkap'],
        'tanggal_lahir' => $row['tanggal_lahir'],
        'jenis_kelamin' => $row['jenis_kelamin'],
        'nama_instansi' => $row['nama_instansi'],
        'jenis_kasus'   => $row['jenis_kasus'],
        'jenis_hukuman' => $row['jenis_hukuman'],
        'status_dpo'    => $row['status_dpo'],
        'jumlah_bounty' => number_format((float) $row['jumlah_bounty'], 0, ',', '.'),
        'foto'          => !empty($row['foto']) && file_exists($row['foto'])
            ? $row['foto']
            : 'https://via.placeholder.com/150',
    ]);
} else {
    echo json_encode(['status' => 'not_found']);
}

