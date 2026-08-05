<?php
// download_pdf.php - Generate poster WANTED (PDF) untuk satu DPO
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

$stmt = $koneksidpogendeng->prepare(
    "SELECT dpo.*, instansi.nama_instansi, jenis_kasus.jenis_kasus AS nama_kasus,
            jenis_hukuman.jenis_hukuman AS nama_hukuman, jenis_hukuman.lama_hukuman,
            COALESCE(bounty.jumlah_bounty, 0) AS jumlah_bounty
     FROM dpo
     LEFT JOIN instansi ON instansi.id = dpo.instansi_id
     LEFT JOIN jenis_kasus ON jenis_kasus.id = dpo.jenis_kasus_id
     LEFT JOIN jenis_hukuman ON jenis_hukuman.id = dpo.jenis_hukuman_id
     LEFT JOIN bounty ON bounty.id_kasus = dpo.jenis_kasus_id
     WHERE dpo.id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    http_response_code(404);
    exit('Data tidak ditemukan.');
}

include __DIR__.'/lib/simple_pdf.php';
include __DIR__.'/lib/audit_log.php';
log_audit('export', 'dpo', $id, "Download poster PDF DPO #$id");

$pdf = new SimplePDF();

// Judul
$pdf->textCenter(70, 28, 'WANTED');
$pdf->textCenter(95, 12, 'DAFTAR PENCARIAN ORANG');

// Foto framed (ambil dari kolom foto, biasanya sudah di-frame)
$foto = $d['foto'];
if (!$foto || !file_exists($foto)) {
    // fallback: buat frame dari foto asli? kalau tidak ada, kosong.
    $foto = null;
}
if ($foto) {
    $pdf->imageJpeg($foto, ($pdf->w - 250) / 2, 130, 250);
}

// Data
$y = $foto ? 420 : 160;
$data = [
    'NAMA'       => $d['nama_lengkap'],
    'NIK'        => $d['nik'],
    'TGL LAHIR'  => $d['tanggal_lahir'],
    'J. KELAMIN' => $d['jenis_kelamin'],
    'KASUS'      => $d['nama_kasus'],
    'HUKUMAN'    => ($d['nama_hukuman'] ?: '') . ($d['lama_hukuman'] ? " ({$d['lama_hukuman']})" : ''),
    'INSTANSI'   => $d['nama_instansi'],
    'BOUNTY'     => 'Rp ' . number_format($d['jumlah_bounty'], 0, ',', '.'),
    'STATUS'     => $d['status_dpo'],
];

$pdf->textCenter($y, 13, '=================================================');
$y += 24;
foreach ($data as $label => $val) {
    $line = $label . ' : ' . $val;
    $pdf->textCenter($y, 12, $line);
    $y += 22;
}
$pdf->textCenter($y + 10, 11, 'Hubungi instansi terdekat apabila melihat orang tersebut.');

$fname = 'wanted_' . preg_replace('/[^A-Za-z0-9 ]/', '', $d['nama_lengkap']) . '.pdf';
$pdf->output($fname);
exit();
?>
