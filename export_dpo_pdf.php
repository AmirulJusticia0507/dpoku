<?php
// export_dpo_pdf.php - Export laporan DPO ke PDF (multi-halaman, tanpa library eksternal)
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
$filterLabel = [];
foreach ($filterMap as $col => $expr) {
    $val = trim($_GET[$col] ?? '');
    if ($val !== '') {
        $where[] = "$expr = ?";
        $params[] = $val;
        $filterLabel[] = ucwords(str_replace('_', ' ', $col)) . ': ' . $val;
    }
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT dpo.nik, dpo.nama_lengkap, dpo.tanggal_lahir, dpo.jenis_kelamin,
               instansi.nama_instansi, jenis_kasus.jenis_kasus, jenis_hukuman.jenis_hukuman,
               jenis_hukuman.lama_hukuman, dpo.alamat, dpo.status_dpo,
               COALESCE(bounty.jumlah_bounty,0) AS jumlah_bounty
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

include __DIR__ . '/lib/simple_pdf.php';
include __DIR__ . '/lib/audit_log.php';
log_audit('export', 'dpo', 0, 'Export laporan PDF DPO (' . count($rows) . ' baris)');

$pdf = new SimplePDF();
$margin = 45;
$lineH  = 14;
$colW   = $pdf->w - ($margin * 2);

$y = 70;
$pdf->textCenter($y, 20, 'LAPORAN DAFTAR PENCARIAN ORANG (DPO)');
$y += 26;
$pdf->textCenter($y, 10, 'Dicetak: ' . date('d M Y H:i'));
$y += 18;
if ($filterLabel) {
    $pdf->text($margin, $y, 9, 'Filter: ' . implode(' | ', $filterLabel));
    $y += 16;
}
$pdf->text($margin, $y, 9, 'Total data: ' . count($rows));
$y += 22;
$pdf->text($margin, $y, 9, str_repeat('-', 110));
$y += 18;

$jk = fn($v) => $v === 'P' ? 'Perempuan' : ($v === 'L' ? 'Laki-laki' : $v);

$no = 1;
foreach ($rows as $r) {
    if ($y > $pdf->h - 70) {
        $pdf->newPage();
        $y = 60;
    }

    $pdf->text($margin, $y, 12, "No. $no");
    $y += 16;

    $fields = [
        'NAMA'        => $r['nama_lengkap'],
        'NIK'         => $r['nik'],
        'TGL LAHIR'   => $r['tanggal_lahir'] ? date('d-m-Y', strtotime($r['tanggal_lahir'])) : '-',
        'J. KELAMIN'  => $jk($r['jenis_kelamin']),
        'KASUS'       => $r['jenis_kasus'],
        'HUKUMAN'     => trim(($r['jenis_hukuman'] ?: '') . ' (' . ($r['lama_hukuman'] ?: '-') . ')'),
        'INSTANSI'    => $r['nama_instansi'],
        'ALAMAT'      => $r['alamat'],
        'BOUNTY'      => 'Rp ' . number_format($r['jumlah_bounty'], 0, ',', '.'),
        'STATUS'      => $r['status_dpo'],
    ];

    foreach ($fields as $label => $val) {
        foreach ($pdf->wrap($label . ' : ' . ($val ?: '-'), 10, $colW) as $ln) {
            if ($y > $pdf->h - 60) {
                $pdf->newPage();
                $y = 60;
            }
            $pdf->text($margin + 10, $y, 10, $ln);
            $y += 13;
        }
    }

    $pdf->text($margin, $y + 2, 9, str_repeat('.', 110));
    $y += 16;
    $no++;
}

$pdf->textCenter($pdf->h - 40, 9, 'DPOKU - Sistem Data Daftar Pencarian Orang');

$fname = 'laporan_dpo_' . date('Ymd_His') . '.pdf';
$pdf->output($fname);
exit();
