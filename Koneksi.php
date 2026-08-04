<?php
$host     = "localhost";
$user     = "root";
$password = ""; // kalau di XAMPP kosong, sesuaikan kalau ada
$database = "db_dpoku"; // ganti dengan nama database kamu

$koneksidpogendeng = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$koneksidpogendeng) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
