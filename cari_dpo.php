<?php
include 'koneksi.php';

$nik = $_GET['nik'] ?? '';
$nama = $_GET['nama'] ?? '';
$instansi = $_GET['instansi'] ?? '';

$query = "SELECT * FROM dpo WHERE 1=1 ";
if ($nik != '') $query .= "AND nik='$nik' ";
if ($nama != '') $query .= "AND nama_lengkap LIKE '%$nama%' ";
if ($instansi != '') $query .= "AND nama_instansi='$instansi' ";

$result = mysqli_query($koneksidpogendeng, $query);

if (mysqli_num_rows($result) > 0) {
    $dpo = mysqli_fetch_assoc($result);
    echo json_encode([
        'status' => 'success',
        'nama_lengkap' => $dpo['nama_lengkap'],
        'tanggal_lahir' => $dpo['tanggal_lahir'],
        'jenis_kelamin' => $dpo['jenis_kelamin'],
        'nama_instansi' => $dpo['nama_instansi'],
        'jenis_kasus' => $dpo['jenis_kasus'],
        'jenis_hukuman' => $dpo['jenis_hukuman'],
        'status_dpo' => $dpo['status_dpo'],
        'foto' => $dpo['foto'] ? $dpo['foto'] : 'https://via.placeholder.com/150'
    ]);
} else {
    echo json_encode(['status' => 'not_found']);
}
?>
