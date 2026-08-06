<?php
// import_pejabat.php - import hasil parsing (pejabat.csv) ke tabel daftar_pejabat
// Jalankan: php import_daftar_pejabat.php
require __DIR__ . '/Koneksi.php';

$csv = $argv[1] ?? '/tmp/opencode/pejabat.csv';
if (!file_exists($csv)) die("pejabat.csv tidak ditemukan: $csv\n");

$rows = array_map('str_getcsv', file($csv));
$header = array_shift($rows);

// kosongkan dulu agar import idempotent
$koneksidpogendeng->exec('TRUNCATE daftar_pejabat RESTART IDENTITY');

$stmt = $koneksidpogendeng->prepare(
    'INSERT INTO daftar_pejabat (nama, jabatan, instansi, keterangan, sumber)
     VALUES (:nama, :jabatan, :instansi, :keterangan, :sumber)'
);

$total = 0;
foreach ($rows as $r) {
    if (count($r) < 5) continue;
    $nama = trim($r[0]);
    $jabatan = trim($r[1]);
    if ($nama === '' && $jabatan === '') continue;
    $stmt->execute([
        ':nama'       => $nama,
        ':jabatan'    => $jabatan,
        ':instansi'   => trim($r[2]),
        ':keterangan' => trim($r[3]),
        ':sumber'     => trim($r[4]),
    ]);
    $total++;
}

echo "Imported: $total baris\n";
$c = $koneksidpogendeng->query('SELECT sumber, COUNT(*) FROM daftar_pejabat GROUP BY sumber ORDER BY sumber');
foreach ($c as $row) echo "  {$row['sumber']}: {$row['count']}\n";

// Isi tabel instansi dari nilai unik daftar_pejabat (idempotent)
$ins = $koneksidpogendeng->exec(
    "INSERT INTO instansi (nama_instansi)
     SELECT DISTINCT instansi FROM daftar_pejabat WHERE instansi <> '' AND instansi <> 'TIDAK DIKETAHUI'
     ON CONFLICT DO NOTHING"
);
echo "Instansi ditambahkan ke tabel instansi: $ins\n";
