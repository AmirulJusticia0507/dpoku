<?php
// export_dpo.php — Export daftar DPO ke CSV (dengan filter opsional)
include 'session.php';
include 'Koneksi.php';

// Proteksi: hanya user login yang boleh export
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

$allow = [
    'nik'           => true,
    'nama_lengkap'  => true,
    'nama_instansi' => true,
    'jenis_kasus'   => true,
    'status_dpo'    => true,
];
$params = [];
$where = [];

foreach ($allow as $col => $_) {
    $val = trim($_GET[$col] ?? '');
    if ($val !== '' && $val !== null) {
        $where[] = "$col ILIKE ?";
        $params[] = "%$val%";
    }
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Prepared statement aman (hindari SQL injection)
$sql = "SELECT dpo.nik, dpo.nama_lengkap, dpo.tanggal_lahir, dpo.jenis_kelamin,
               dpo.nama_instansi, dpo.jenis_kasus, dpo.jenis_hukuman,
               dpo.no_hp, dpo.email, dpo.alamat, dpo.status_dpo,
               COALESCE(bounty.jumlah_bounty,0) AS jumlah_bounty,
               dpo.created_at, dpo.updated_at
        FROM dpo
        LEFT JOIN bounty ON bounty.id_kasus = CASE WHEN dpo.jenis_kasus ~ '^[0-9]+$' THEN dpo.jenis_kasus::int ELSE NULL END
        $whereSql
        ORDER BY dpo.created_at DESC";

$stmt = $koneksidpogendeng->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit('Gagal menyiapkan statement.');
}

$stmt->execute($params);

// Header CSV: gunakan nama file + UTF-8 BOM agar di Excel tidak korup
$ts = date('Ymd_His');
$filename = 'dpo_export_' . $ts . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
// BOM utf-8 (supaya Excel bahas Indonesia)
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// Kolom header label user-friendly
fputcsv($out, [
    'NIK', 'Nama Lengkap', 'Tgl Lahir', 'Jenis Kelamin',
    'Instansi', 'Jenis Kasus', 'Jenis Hukuman',
    'No HP', 'Email', 'Alamat', 'Status DPO',
    'Jumlah Bounty (Rp)', 'Dibuat', 'Diupdate',
]);

$rows = 0;
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['nik'],
        $row['nama_lengkap'],
        $row['tanggal_lahir'],
        $row['jenis_kelamin'] === 'P' ? 'Perempuan' : ($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : $row['jenis_kelamin']),
        $row['nama_instansi'],
        $row['jenis_kasus'],
        $row['jenis_hukuman'],
        $row['no_hp'],
        $row['email'],
        $row['alamat'],
        $row['status_dpo'],
        number_format((float) $row['jumlah_bounty'], 0, ',', '.'),
        $row['created_at'],
        $row['updated_at'],
    ]);
    $rows++;
}
fclose($out);
exit();
