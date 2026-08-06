<?php
include 'Koneksi.php';
include 'session.php';
if (is_viewer()) { header("Location: index.php"); exit; }
?>
<?php $page_title = 'Form Input Instansi'; ?>
<?php include 'Header.php'; ?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Form Input Instansi</h3>

  <?php
  // Proses Tambah Data
  if (isset($_POST['submit'])) {
    $nama_instansi = $_POST['nama_instansi'];
    $keterangan_instansi = $_POST['keterangan_instansi'];

    $stmt = $koneksidpogendeng->prepare("INSERT INTO instansi (nama_instansi, keterangan_instansi) VALUES (?, ?)");
    if ($stmt->execute([$nama_instansi, $keterangan_instansi])) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data.</div>';
    }
  }

  // Proses Update Data
  if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama_instansi = $_POST['nama_instansi'];
    $keterangan_instansi = $_POST['keterangan_instansi'];

    $stmt = $koneksidpogendeng->prepare("UPDATE instansi SET nama_instansi=?, keterangan_instansi=? WHERE id=?");
    if ($stmt->execute([$nama_instansi, $keterangan_instansi, $id])) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil diupdate!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal update data.</div>';
    }
  }

  // Proses Hapus Data
  if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $koneksidpogendeng->prepare("DELETE FROM instansi WHERE id=?");
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
    $stmt = $koneksidpogendeng->prepare("SELECT * FROM instansi WHERE id=?");
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
      <label for="nama_instansi" class="block text-sm font-medium text-gray-700 mb-1">Nama Instansi</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="nama_instansi" name="nama_instansi" required
             value="<?= $editData ? $editData['nama_instansi'] : '' ?>">
    </div>
    <div class="mb-3">
      <label for="keterangan_instansi" class="block text-sm font-medium text-gray-700 mb-1">Keterangan Instansi</label>
      <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="keterangan_instansi" name="keterangan_instansi" rows="4"><?= $editData ? $editData['keterangan_instansi'] : '' ?></textarea>
    </div>
    <?php if ($editData) { ?>
      <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition">Update</button>
      <a href="Inputinstansi.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal</a>
    <?php } else { ?>
      <button type="submit" name="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
    <?php } ?>
  </form>

  <hr class="my-6 border-gray-200">
  <h4 class="text-lg font-semibold mb-3">Data Instansi</h4>
  <div class="overflow-x-auto">
    <table id="instansiTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">No</th>
          <th class="px-3 py-2">Nama Instansi</th>
          <th class="px-3 py-2">Keterangan</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = $koneksidpogendeng->query("SELECT * FROM instansi");
        while ($d = $data->fetch()) {
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2"><?= $d['nama_instansi'] ?></td>
            <td class="px-3 py-2"><?= $d['keterangan_instansi'] ?></td>
            <td class="px-3 py-2">
              <a href="Inputinstansi.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <a href="Inputinstansi.php?hapus=<?= $d['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded-lg transition" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#instansiTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>
