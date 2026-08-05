<?php 
include 'Koneksi.php';
$page_title = 'Form Input Bounty';
include 'Header.php';
?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4 flex flex-wrap justify-between items-center gap-2">
    <span>Form Input Bounty</span>
    <span class="flex gap-2">
      <a href="export_dpo.php" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition"><i class="fas fa-file-csv"></i> Export CSV DPO</a>
      <a href="audit_log.php" class="bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition"><i class="fas fa-history"></i> Audit Log</a>
    </span>
  </h3>

  <?php
  // Proses Tambah Data
  if (isset($_POST['submit'])) {
     $jumlah_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_bounty']);
     $id_kasus = (int) $_POST['id_kasus'];
     $id_hukuman = (int) $_POST['id_hukuman'];
     $createdBy = (int) ($_SESSION['user_id'] ?? 0);

     $stmt = $koneksidpogendeng->prepare(
         "INSERT INTO bounty (jumlah_bounty, id_kasus, id_hukuman, created_by)
          VALUES (?, ?, ?, ?)");
     if ($stmt->execute([$jumlah_bounty, $id_kasus, $id_hukuman, $createdBy])) {
         $newId = (int) $koneksidpogendeng->lastInsertId();
         include __DIR__.'/lib/audit_log.php';
         log_audit('create', 'bounty', $newId, "Tambah bounty kasus=$id_kasus hukuman=$id_hukuman");
         echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
     } else {
         echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data.</div>';
     }
  }

  // Proses Update Data
  if (isset($_POST['update'])) {
     $id = (int) $_POST['id'];
     $jumlah_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_bounty']);
     $id_kasus = (int) $_POST['id_kasus'];
     $id_hukuman = (int) $_POST['id_hukuman'];
     $upBy = (int) ($_SESSION['user_id'] ?? 0);

     $stmt = $koneksidpogendeng->prepare(
         "UPDATE bounty SET jumlah_bounty=?, id_kasus=?, id_hukuman=?, updated_by=? WHERE id=?");
     if ($stmt->execute([$jumlah_bounty, $id_kasus, $id_hukuman, $upBy, $id])) {
         include __DIR__.'/lib/audit_log.php';
         log_audit('update', 'bounty', $id, "Update bounty -> kasus=$id_kasus hukuman=$id_hukuman");
         echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil diupdate!</div>';
     } else {
         echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal update data.</div>';
     }
  }

  // Proses Hapus Data
  if (isset($_GET['hapus'])) {
     $id = (int) $_GET['hapus'];
     $stmt = $koneksidpogendeng->prepare("DELETE FROM bounty WHERE id=?");
     if ($stmt->execute([$id])) {
         include __DIR__.'/lib/audit_log.php';
         log_audit('delete', 'bounty', $id, 'Hapus bounty');
         echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil dihapus!</div>';
     } else {
         echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menghapus data.</div>';
     }
  }

  // Ambil data jika mau edit
  $editData = null;
  if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $koneksidpogendeng->prepare("SELECT * FROM bounty WHERE id=?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
  }
  ?>

  <!-- Form Input / Edit -->
  <form action="" method="POST" class="max-w-2xl">
    <?php if ($editData) { ?>
      <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <?php } ?>
    <div class="mb-3">
      <label for="jumlah_bounty" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bounty</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none rupiah" id="jumlah_bounty" name="jumlah_bounty" required
        value="<?= $editData ? number_format($editData['jumlah_bounty'], 0, ',', '.') : '' ?>">
    </div>

    <div class="mb-3">
      <label for="id_kasus" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kasus</label>
      <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="id_kasus" name="id_kasus" required>
        <option value="">Pilih Kasus</option>
        <?php
        $kasus = $koneksidpogendeng->query("SELECT * FROM jenis_kasus");
        while ($k = $kasus->fetch()) {
          $selected = $editData && $editData['id_kasus'] == $k['id'] ? 'selected' : '';
          echo "<option value='{$k['id']}' $selected>{$k['jenis_kasus']}</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="id_hukuman" class="block text-sm font-medium text-gray-700 mb-1">Jenis Hukuman</label>
      <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="id_hukuman" name="id_hukuman" required>
        <option value="">Pilih Hukuman</option>
        <?php
        $hukuman = $koneksidpogendeng->query("SELECT * FROM jenis_hukuman");
        while ($h = $hukuman->fetch()) {
          $selected = $editData && $editData['id_hukuman'] == $h['id'] ? 'selected' : '';
          echo "<option value='{$h['id']}' $selected>{$h['jenis_hukuman']}</option>";
        }
        ?>
      </select>
    </div>

    <?php if ($editData) { ?>
      <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition">Update</button>
      <a href="Bounty.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal</a>
    <?php } else { ?>
      <button type="submit" name="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
    <?php } ?>
  </form>

  <hr class="my-6 border-gray-200">

  <h4 class="text-lg font-semibold mb-3">Data Bounty</h4>
  <div class="overflow-x-auto">
    <table id="bountyTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">No</th>
          <th class="px-3 py-2">Jumlah Bounty</th>
          <th class="px-3 py-2">Jenis Kasus</th>
          <th class="px-3 py-2">Jenis Hukuman</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = $koneksidpogendeng->query("SELECT bounty.*, jenis_kasus.jenis_kasus, jenis_hukuman.jenis_hukuman 
                                                  FROM bounty 
                                                  LEFT JOIN jenis_kasus ON bounty.id_kasus = jenis_kasus.id
                                                  LEFT JOIN jenis_hukuman ON bounty.id_hukuman = jenis_hukuman.id");
        while ($d = $data->fetch()) {
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2">Rp <?= number_format($d['jumlah_bounty'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= $d['jenis_kasus'] ?></td>
            <td class="px-3 py-2"><?= $d['jenis_hukuman'] ?></td>
            <td class="px-3 py-2">
              <a href="Bounty.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <a href="Bounty.php?hapus=<?= $d['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded-lg transition" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#bountyTable').DataTable();

    // Auto Format Rupiah
    $('.rupiah').on('keyup', function() {
      let value = $(this).val().replace(/[^,\d]/g, '').toString();
      let split = value.split(',');
      let sisa = split[0].length % 3;
      let rupiah = split[0].substr(0, sisa);
      let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }

      rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
      $(this).val(rupiah);
    });
  });
</script>

<?php include 'Footer.php'; ?>
