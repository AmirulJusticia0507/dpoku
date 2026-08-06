<?php
// import_kabinet_kmp.php - ganti baris Kabinet Merah Putih 2024-2029 yang ada
// dengan hasil parsing Wikipedia (kabinet_kmp.csv: nama,jabatan,instansi,keterangan)
// Jalankan: php import_kabinet_kmp.php [path-kabinet_kmp.csv]
require __DIR__ . '/Koneksi.php';

$csv = $argv[1] ?? '/tmp/opencode/kabinet_kmp.csv';
if (!file_exists($csv)) die("kabinet_kmp.csv tidak ditemukan: $csv\n");

$rows = array_map('str_getcsv', file($csv));
$header = array_shift($rows);
if ($header !== ['nama', 'jabatan', 'instansi', 'keterangan']) die("format header tidak sesuai\n");

$keterangan = 'Kabinet Merah Putih 2024-2029';
$del = $koneksidpogendeng->exec(
    "DELETE FROM daftar_pejabat WHERE sumber = 'KABINET' AND keterangan = " .
    $koneksidpogendeng->quote($keterangan)
);
echo "Baris lama dihapus: $del\n";

$stmt = $koneksidpogendeng->prepare(
    'INSERT INTO daftar_pejabat (nama, jabatan, instansi, keterangan, sumber)
     VALUES (:nama, :jabatan, :instansi, :keterangan, :sumber)'
);

$total = 0;
foreach ($rows as $r) {
    if (count($r) < 4) continue;
    $nama = trim($r[0]);
    $jabatan = trim($r[1]);
    if ($nama === '' && $jabatan === '') continue;
    $stmt->execute([
        ':nama'       => $nama,
        ':jabatan'    => $jabatan,
        ':instansi'   => trim($r[2]),
        ':keterangan' => trim($r[3]),
        ':sumber'     => 'KABINET',
    ]);
    $total++;
}

echo "Imported: $total baris\n";
$c = $koneksidpogendeng->query('SELECT sumber, COUNT(*) FROM daftar_pejabat GROUP BY sumber ORDER BY sumber');
foreach ($c as $row) echo "  {$row['sumber']}: {$row['count']}\n";

$ins = $koneksidpogendeng->exec(
    "INSERT INTO instansi (nama_instansi)
     SELECT DISTINCT instansi FROM daftar_pejabat WHERE instansi <> '' AND instansi <> 'TIDAK DIKETAHUI'
     ON CONFLICT DO NOTHING"
);
echo "Instansi ditambahkan ke tabel instansi: $ins\n";
