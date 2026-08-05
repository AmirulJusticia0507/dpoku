<?php
$page_title = 'Edit DPO';
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Ambil data DPO
$stmt = $koneksidpogendeng->prepare("SELECT * FROM dpo WHERE id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    die("Data DPO tidak ditemukan.");
}

// Fetch data master untuk select option
$instansis = $koneksidpogendeng->query("SELECT * FROM instansi ORDER BY nama_instansi")->fetchAll();
$kasusList = $koneksidpogendeng->query("SELECT * FROM jenis_kasus ORDER BY jenis_kasus")->fetchAll();
$hukumanList = $koneksidpogendeng->query("SELECT * FROM jenis_hukuman ORDER BY jenis_hukuman, lama_hukuman")->fetchAll();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nik              = $_POST['nik'];
    $nama_lengkap     = $_POST['nama_lengkap'];
    $tanggal_lahir    = $_POST['tanggal_lahir'] ?: null;
    $jenis_kelamin    = $_POST['jenis_kelamin'];
    $instansi_id      = (int) ($_POST['instansi_id'] ?? 0) ?: null;
    $jenis_kasus_id   = (int) ($_POST['jenis_kasus_id'] ?? 0) ?: null;
    $jenis_hukuman_id = (int) ($_POST['jenis_hukuman_id'] ?? 0) ?: null;
    $no_hp            = $_POST['no_hp'];
    $email            = $_POST['email'];
    $media_sosial     = $_POST['media_sosial'];
    $alamat           = $_POST['alamat'];
    $status_dpo       = $_POST['status_dpo'];
    $userId           = (int) $_SESSION['user_id'];

    // Foto baru (opsional)
    $foto = $d['foto'];
    if (!empty($_FILES['foto']['tmp_name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
        $allowedExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $imageInfo = @getimagesize($_FILES['foto']['tmp_name']);
        if (isset($allowedExt[$ext]) && $imageInfo !== false && in_array($imageInfo['mime'], $allowedExt, true)
            && $_FILES['foto']['size'] <= 3 * 1024 * 1024) {

            if (!file_exists('uploads/')) mkdir('uploads/', 0755, true);
            $uploaded_image = match ($imageInfo[2]) {
                IMAGETYPE_PNG  => @imagecreatefrompng($_FILES['foto']['tmp_name']),
                IMAGETYPE_WEBP => @imagecreatefromwebp($_FILES['foto']['tmp_name']),
                default        => @imagecreatefromjpeg($_FILES['foto']['tmp_name']),
            };
            if ($uploaded_image) {
                $target_file = 'uploads/' . bin2hex(random_bytes(8)) . '_' . time() . '.jpg';
                imagejpeg($uploaded_image, $target_file, 90);

                $frame = 'wanted_new.png';
                $frame_image = @imagecreatefrompng($frame);
                if ($frame_image) {
                    $fw = imagesx($frame_image); $fh = imagesy($frame_image);
                    $uw = imagesx($uploaded_image); $uh = imagesy($uploaded_image);
                    $new_image = imagecreatetruecolor($fw, $fh);
                    imagecopyresampled($new_image, $uploaded_image, 0, 0, 0, 0, $fw, $fh, $uw, $uh);
                    imagecopy($new_image, $frame_image, 0, 0, 0, 0, $fw, $fh);
                    $framed_file = 'uploads/' . "framed_" . basename($target_file);
                    imagejpeg($new_image, $framed_file, 90);
                    imagedestroy($new_image); imagedestroy($frame_image);
                    $foto = $framed_file;
                } else {
                    $foto = $target_file;
                }
                imagedestroy($uploaded_image);

                // Hapus file foto lama dari disk (framed + original)
                if (!empty($d['foto']) && strpos($d['foto'], 'uploads/') === 0) {
                    if ($d['foto'] !== $foto && file_exists($d['foto'])) unlink($d['foto']);
                    $oldOrig = 'uploads/' . ltrim(str_replace('framed_', '', basename($d['foto'])), '/');
                    if ($oldOrig !== $foto && file_exists($oldOrig)) unlink($oldOrig);
                }
            }
        } else {
            echo "<script>alert('Foto tidak valid atau melebihi 3 MB.');</script>";
        }
    }

    $upd = $koneksidpogendeng->prepare(
        "UPDATE dpo SET nik=?, nama_lengkap=?, tanggal_lahir=?, jenis_kelamin=?, instansi_id=?,
                jenis_kasus_id=?, jenis_hukuman_id=?, no_hp=?, email=?, media_sosial=?, alamat=?, status_dpo=?, foto=?
         WHERE id=?");
    $upd->execute([
        $nik, $nama_lengkap, $tanggal_lahir, $jenis_kelamin, $instansi_id,
        $jenis_kasus_id, $jenis_hukuman_id, $no_hp, $email, $media_sosial, $alamat, $status_dpo, $foto, $id,
    ]);

    // Log perubahan status
    if ($status_dpo !== $d['status_dpo']) {
        $lg = $koneksidpogendeng->prepare(
            "INSERT INTO dpo_status_log (dpo_id, status_lama, status_baru, changed_by) VALUES (?, ?, ?, ?)");
        $lg->execute([$id, $d['status_dpo'], $status_dpo, $userId]);
    }

    include __DIR__.'/lib/audit_log.php';
    log_audit('update', 'dpo', $id, "Update DPO #$id status=$status_dpo");

    echo "<script>alert('Data DPO berhasil diupdate!'); window.location.href='detail_dpo.php?id=$id';</script>";
    exit();
}

include 'Header.php';
?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Edit DPO #<?= $id ?> - <?= htmlspecialchars($d['nama_lengkap']) ?></h3>

  <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
      <input type="text" name="nik" required maxlength="16" pattern="[0-9]{16}" value="<?= htmlspecialchars($d['nik']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
      <input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($d['nama_lengkap']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
      <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($d['tanggal_lahir']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
      <select name="jenis_kelamin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">-- Pilih --</option>
        <option value="Laki-laki" <?= $d['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
        <option value="Perempuan" <?= $d['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Instansi</label>
      <select name="instansi_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">-- Pilih Instansi --</option>
        <?php foreach ($instansis as $i): ?>
          <option value="<?= $i['id'] ?>" <?= (int) $d['instansi_id'] === (int) $i['id'] ? 'selected' : '' ?>><?= htmlspecialchars($i['nama_instansi']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kasus</label>
      <select name="jenis_kasus_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">-- Pilih Kasus --</option>
        <?php foreach ($kasusList as $k): ?>
          <option value="<?= $k['id'] ?>" <?= (int) $d['jenis_kasus_id'] === (int) $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['jenis_kasus']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hukuman</label>
      <select name="jenis_hukuman_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">-- Pilih Hukuman --</option>
        <?php foreach ($hukumanList as $h): ?>
          <option value="<?= $h['id'] ?>" <?= (int) $d['jenis_hukuman_id'] === (int) $h['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($h['jenis_hukuman']) ?> (<?= htmlspecialchars($h['lama_hukuman']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Status DPO</label>
      <select name="status_dpo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="BURON" <?= $d['status_dpo'] === 'BURON' ? 'selected' : '' ?>>BURON</option>
        <option value="TERTANGKAP" <?= $d['status_dpo'] === 'TERTANGKAP' ? 'selected' : '' ?>>TERTANGKAP</option>
        <option value="MENINGGAL DUNIA" <?= $d['status_dpo'] === 'MENINGGAL DUNIA' ? 'selected' : '' ?>>MENINGGAL DUNIA</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
      <input type="text" name="no_hp" value="<?= htmlspecialchars($d['no_hp']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($d['email']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">Media Sosial</label>
      <input type="text" name="media_sosial" value="<?= htmlspecialchars($d['media_sosial']) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
      <textarea name="alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($d['alamat']) ?></textarea>
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">Ganti Foto (opsional, maks 3 MB)</label>
      <div class="flex items-center gap-3">
        <img src="<?= htmlspecialchars($d['foto']) ?>" class="h-24 w-24 object-cover rounded-lg" alt="foto">
        <input type="file" name="foto" accept="image/*"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm">
      </div>
    </div>

    <div class="md:col-span-2 flex gap-2">
      <button type="submit" name="update" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-save mr-1"></i> Simpan Perubahan
      </button>
      <a href="detail_dpo.php?id=<?= $id ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal</a>
    </div>
  </form>
</div>

<?php include 'Footer.php'; ?>
