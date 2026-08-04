<?php
session_start();
include 'koneksi.php'; // koneksi DB kamu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $query = mysqli_query($koneksidpogendeng, "SELECT * FROM user WHERE username='$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $_SESSION['reset_username'] = $username;
        header("Location: reset_password.php");
        exit();
    } else {
        echo "Username tidak ditemukan!";
    }
}
?>
