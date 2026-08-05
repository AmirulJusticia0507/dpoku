<?php
include 'Koneksi.php';

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
    $foto_name = $_FILES['foto']['name'] ?? '';
    $foto_tmp  = $_FILES['foto']['tmp_name'] ?? '';
    $foto_error = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;
    $foto_size  = $_FILES['foto']['size'] ?? 0;
    $upload_dir = 'uploads/';

    // --- Validasi foto upload ---
    if ($foto_error === UPLOAD_ERR_NO_FILE || empty($foto_tmp) || !is_uploaded_file($foto_tmp)) {
        die("<script>alert('Foto wajib diupload.'); window.history.back();</script>");
    }
    if ($foto_error !== UPLOAD_ERR_OK) {
        die("<script>alert('Upload foto gagal (kode error: $foto_error).'); window.history.back();</script>");
    }

    // Ekstensi & ukuran
    $allowedExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
    if (!isset($allowedExt[$ext])) {
        die("<script>alert('Format foto tidak didukung. Gunakan JPG/JPEG/PNG/WebP.'); window.history.back();</script>");
    }

    // Ukuran maks 3 MB
    if ($foto_size > 3 * 1024 * 1024) {
        die("<script>alert('Foto melebihi 3 MB.'); window.history.back();</script>");
    }

    // Verifikasi MIME via getimagesize (bukan hanya ekstensi)
    $imageInfo = @getimagesize($foto_tmp);
    if ($imageInfo === false || !isset($allowedExt[$imageInfo['mime']])) {
        die("<script>alert('File bukan gambar yang valid.'); window.history.back();</script>");
    }

    // Pastikan folder uploads ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Filename acak (hindari overwrite & path traversal)
    $target_file = $upload_dir . bin2hex(random_bytes(8)) . '_' . time() . '.jpg';

    // Simpan original foto dulu (convert ke JPG agar konsisten dengan GD frame)
    $uploaded_image = ($imageInfo[2] === IMAGETYPE_PNG) ? imagecreatefrompng($foto_tmp) : imagecreatefromjpeg($foto_tmp);
    if (!$uploaded_image) {
        die("<script>alert('Gagal memproses gambar.'); window.history.back();</script>");
    }

    // Simpan original (jpeg) untuk fallback
    imagejpeg($uploaded_image, $target_file, 90);

    // Resize jika ukuran tidak sesuai dengan frame
    $frame = 'wanted_new.png';
    $frame_image = @imagecreatefrompng($frame);
    if ($frame_image) {
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
        imagedestroy($new_image);
        imagedestroy($frame_image);
    } else {
        $framed_file = $target_file;
    }
    imagedestroy($uploaded_image);

    // Insert ke database (prepared statement aman)
    $createdBy = (int) ($_SESSION['user_id'] ?? 0);

    // Validasi unik NIK & email (cek duplikat)
    $cek = $koneksidpogendeng->prepare("SELECT id FROM dpo WHERE nik = ? OR email = ? LIMIT 1");
    $cek->execute([$nik, $email]);
    if ($cek->fetch()) {
        include __DIR__.'/lib/audit_log.php';
        log_audit('create_failed', 'dpo', null, "Duplikat NIK=$nik / email=$email ditolak");
        echo "<script>alert('NIK atau email sudah terdaftar. Upload ulang dengan data berbeda.');window.location.href='inputdpo.php';</script>";
        exit();
    }

    $stmt = $koneksidpogendeng->prepare(
        "INSERT INTO dpo (nik, nama_lengkap, tanggal_lahir, jenis_kelamin, nama_instansi,
                jenis_kasus, jenis_hukuman, no_hp, email, media_sosial, alamat, status_dpo, foto, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $nik, $nama_lengkap, $tanggal_lahir ?: null, $jenis_kelamin,
        $nama_instansi, $jenis_kasus, $jenis_hukuman, $no_hp, $email, $media_sosial,
        $alamat, $status_dpo, $framed_file, $createdBy,
    ]);

    if ($stmt->rowCount() > 0) {
        $newId = (int) $koneksidpogendeng->lastInsertId();
        include __DIR__.'/lib/audit_log.php';
        log_audit('create', 'dpo', $newId, "Tambah DPO NIK=$nik nama=$nama_lengkap instansi=$nama_instansi");
        echo "<script>alert('Data DPO berhasil ditambahkan!'); window.location.href='inputdpo.php';</script>";
    } else {
        echo "Error: insert gagal";
    }
} else {
    echo "Request tidak valid.";
}
?>
