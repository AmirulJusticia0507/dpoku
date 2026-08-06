<?php
// export_dpo_excel.php - Export laporan DPO ke Excel (.xls via tabel HTML)
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

$filterMap = [
    'nik'           => 'dpo.nik',
    'nama_lengkap'  => 'dpo.nama_lengkap',
    'nama_instansi' => 'instansi.nama_instansi',
    'jenis_kasus'   => 'jenis_kasus.jenis_kasus',
    'status_dpo'    => 'dpo.status_dpo',
];
$params = [];
$where = [];
foreach ($filterMap as $col => $expr) {
    $val = trim($_GET[$col] ?? '');
    if ($val !== '') {
        $where[] = "$expr = ?";
        $params[] = $val;
    }
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT dpo.nik, dpo.nama_lengkap, dpo.tanggal_lahir, dpo.jenis_kelamin,
               instansi.nama_instansi, jenis_kasus.jenis_kasus, jenis_hukuman.jenis_hukuman,
               jenis_hukuman.lama_hukuman, dpo.no_hp, dpo.email, dpo.alamat, dpo.status_dpo,
               COALESCE(bounty.jumlah_bounty,0) AS jumlah_bounty, dpo.created_at, dpo.updated_at
        FROM dpo
        LEFT JOIN instansi ON instansi.id = dpo.instansi_id
        LEFT JOIN jenis_kasus ON jenis_kasus.id = dpo.jenis_kasus_id
        LEFT JOIN jenis_hukuman ON jenis_hukuman.id = dpo.jenis_hukuman_id
        LEFT JOIN bounty ON bounty.id_kasus = dpo.jenis_kasus_id
        $whereSql
        ORDER BY dpo.created_at DESC";
$stmt = $koneksidpogendeng->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$ts = date('Ymd_His');
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="dpo_laporan_' . $ts . '.xls"');

$jk = fn($v) => $v === 'P' ? 'Perempuan' : ($v === 'L' ? 'Laki-laki' : $v);
?>
<html>
<head><meta charset="utf-8"></head>
<body>
<table border="1">
  <thead>
    <tr>
      <th>No</th><th>NIK</th><th>Nama Lengkap</th><th>Tgl Lahir</th><th>Jenis Kelamin</th>
      <th>Instansi</th><th>Jenis Kasus</th><th>Jenis Hukuman</th><th>Lama Hukuman</th>
      <th>No HP</th><th>Email</th><th>Alamat</th><th>Status DPO</th>
      <th>Bounty (Rp)</th><th>Dibuat</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($rows as $r): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['nik']) ?></td>
        <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
        <td><?= htmlspecialchars($r['tanggal_lahir']) ?></td>
        <td><?= htmlspecialchars($jk($r['jenis_kelamin'])) ?></td>
        <td><?= htmlspecialchars($r['nama_instansi']) ?></td>
        <td><?= htmlspecialchars($r['jenis_kasus']) ?></td>
        <td><?= htmlspecialchars($r['jenis_hukuman']) ?></td>
        <td><?= htmlspecialchars($r['lama_hukuman']) ?></td>
        <td><?= htmlspecialchars($r['no_hp']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['alamat']) ?></td>
        <td><?= htmlspecialchars($r['status_dpo']) ?></td>
        <td><?= number_format((float) $r['jumlah_bounty'], 0, ',', '.') ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>
