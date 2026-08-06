<?php
include 'Koneksi.php';
include 'session.php';
if (is_viewer()) { header("Location: index.php"); exit; }
?>
<?php $page_title = 'Form Input Jenis Kasus'; ?>
<?php include 'Header.php'; ?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Form Input Jenis Kasus</h3>

  <?php
  // Proses Tambah Data
  if (isset($_POST['submit'])) {
    $jenis_kasus = $_POST['jenis_kasus'];
    $deskripsi_kasus = $_POST['deskripsi_kasus'];

    $stmt = $koneksidpogendeng->prepare("INSERT INTO jenis_kasus (jenis_kasus, deskripsi_kasus) VALUES (?, ?)");
    if ($stmt->execute([$jenis_kasus, $deskripsi_kasus])) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data.</div>';
    }
  }

  // Proses Update Data
  if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $jenis_kasus = $_POST['jenis_kasus'];
    $deskripsi_kasus = $_POST['deskripsi_kasus'];

    $stmt = $koneksidpogendeng->prepare("UPDATE jenis_kasus SET jenis_kasus=?, deskripsi_kasus=? WHERE id=?");
    if ($stmt->execute([$jenis_kasus, $deskripsi_kasus, $id])) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil diupdate!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal update data.</div>';
    }
  }

  // Proses Hapus Data
  if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $koneksidpogendeng->prepare("DELETE FROM jenis_kasus WHERE id=?");
    if ($stmt->execute([$id])) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil dihapus!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menghapus data.</div>';
    }
  }

  // Ambil data jika mau edit
  $editData = null;
  if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $koneksidpogendeng->prepare("SELECT * FROM jenis_kasus WHERE id=?");
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
      <label for="jenis_kasus" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kasus</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="jenis_kasus" name="jenis_kasus" required
             value="<?= $editData ? $editData['jenis_kasus'] : '' ?>">
    </div>
    <div class="mb-3">
      <label for="deskripsi_kasus" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Kasus</label>
      <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="deskripsi_kasus" name="deskripsi_kasus" rows="4"><?= $editData ? $editData['deskripsi_kasus'] : '' ?></textarea>
    </div>
    <?php if ($editData) { ?>
      <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition">Update</button>
      <a href="Inputjeniskasus.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal</a>
    <?php } else { ?>
      <button type="submit" name="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
    <?php } ?>
  </form>

  <hr class="my-6 border-gray-200">
  <h4 class="text-lg font-semibold mb-3">Data Jenis Kasus</h4>
  <div class="overflow-x-auto">
    <table id="kasusTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">No</th>
          <th class="px-3 py-2">Jenis Kasus</th>
          <th class="px-3 py-2">Deskripsi</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = $koneksidpogendeng->query("SELECT * FROM jenis_kasus");
        while ($d = $data->fetch()) {
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2"><?= $d['jenis_kasus'] ?></td>
            <td class="px-3 py-2"><?= $d['deskripsi_kasus'] ?></td>
            <td class="px-3 py-2">
              <a href="Inputjeniskasus.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <a href="Inputjeniskasus.php?hapus=<?= $d['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded-lg transition" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#kasusTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>
