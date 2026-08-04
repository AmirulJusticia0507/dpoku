<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $nik             = $_POST['nik'];
    $nama_lengkap    = $_POST['nama_lengkap'];
    $tanggal_lahir   = $_POST['tanggal_lahir'];
    $jenis_kelamin   = $_POST['jenis_kelamin'];
    $nama_instansi   = $_POST['nama_instansi'];
    $jenis_kasus     = $_POST['jenis_kasus'];
    $jenis_hukuman   = $_POST['jenis_hukuman'];
    $no_hp           = $_POST['no_hp'];
    $email           = $_POST['email'];
    $media_sosial    = $_POST['media_sosial'];
    $alamat          = $_POST['alamat'];
    $status_dpo      = $_POST['status_dpo'];

    // Foto Upload + Frame
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $upload_dir = 'uploads/';

    // Pastikan folder uploads ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $target_file = $upload_dir . time() . "_" . basename($foto_name);

    // Simpan original foto dulu
    if (move_uploaded_file($foto_tmp, $target_file)) {
        // Proses overlay frame wanted
        $frame = 'wanted_new.png';
        $frame_image = imagecreatefrompng($frame);
        $uploaded_image = imagecreatefromjpeg($target_file);

        // Resize jika ukuran tidak sesuai
        $frame_width = imagesx($frame_image);
        $frame_height = imagesy($frame_image);
        $uploaded_width = imagesx($uploaded_image);
        $uploaded_height = imagesy($uploaded_image);

        // Bikin canvas baru
        $new_image = imagecreatetruecolor($frame_width, $frame_height);
        imagecopyresampled($new_image, $uploaded_image, 0, 0, 0, 0, $frame_width, $frame_height, $uploaded_width, $uploaded_height);
        imagecopy($new_image, $frame_image, 0, 0, 0, 0, $frame_width, $frame_height);

        // Simpan hasil frame jadi file baru
        $framed_file = $upload_dir . "framed_" . basename($target_file);
        imagejpeg($new_image, $framed_file, 90);

        // Free memory
        imagedestroy($uploaded_image);
        imagedestroy($frame_image);
        imagedestroy($new_image);

        // Insert ke database
        $sql = "INSERT INTO dpo (nik, nama_lengkap, tanggal_lahir, jenis_kelamin, nama_instansi, jenis_kasus, jenis_hukuman, no_hp, email, media_sosial, alamat, status_dpo, foto)
                VALUES ('$nik', '$nama_lengkap', '$tanggal_lahir', '$jenis_kelamin', '$nama_instansi', '$jenis_kasus', '$jenis_hukuman', '$no_hp', '$email', '$media_sosial', '$alamat', '$status_dpo', '$framed_file')";

        if (mysqli_query($koneksidpogendeng, $sql)) {
            echo "<script>alert('Data DPO berhasil ditambahkan!'); window.location.href='inputdpo.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($koneksidpogendeng);
        }
    } else {
        echo "Gagal upload foto.";
    }
} else {
    echo "Request tidak valid.";
}
?>
