<?php
// login_process.php
session_start();
include 'Koneksi.php'; // Pastikan Anda memiliki koneksi database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksidpogendeng, $_POST['username']);
    $password = $_POST['password'];

    // Query cek user
    $stmt = $koneksidpogendeng->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Cek user ada atau nggak
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password hash (bcrypt)
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php"); // Redirect ke index.php
            exit();
        } else {
            echo "<script>alert('Password salah!');window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!');window.location='login.php';</script>";
    }
}
?>