<?php include 'Koneksi.php'; ?>
<?php $page_title = 'Form Input Jenis Hukuman'; ?>
<?php include 'Header.php'; ?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Form Input Jenis Hukuman</h3>

  <?php
  // Cek Proses Edit
  $edit_data = null;
  if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $result = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman WHERE id='$id_edit'");
    $edit_data = mysqli_fetch_assoc($result);
  }

  // Proses Hapus
  if (isset($_GET['delete'])) {
    $id_delete = $_GET['delete'];
    mysqli_query($koneksidpogendeng, "DELETE FROM jenis_hukuman WHERE id='$id_delete'");
    echo "<script>alert('Data berhasil dihapus!'); window.location='Inputjenishukuman.php';</script>";
  }

  // Proses Simpan / Update
  if (isset($_POST['submit'])) {
    $jenis_hukuman = $_POST['jenis_hukuman'];
    $lama_hukuman = $_POST['lama_hukuman'];
    $vonis_putusan = $_POST['vonis_putusan'];
    $status = $_POST['status'];

    if (isset($_POST['id'])) { // Edit Mode
      $id = $_POST['id'];
      $query = "UPDATE jenis_hukuman SET jenis_hukuman='$jenis_hukuman', lama_hukuman='$lama_hukuman', vonis_putusan='$vonis_putusan', status='$status' WHERE id='$id'";
    } else { // Input Baru
      $query = "INSERT INTO jenis_hukuman (jenis_hukuman, lama_hukuman, vonis_putusan, status) VALUES ('$jenis_hukuman', '$lama_hukuman', '$vonis_putusan', '$status')";
    }

    if (mysqli_query($koneksidpogendeng, $query)) {
      echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
    } else {
      echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data.</div>';
    }
  }
  ?>

  <!-- Form Input/Edit -->
  <form method="POST" class="max-w-2xl">
    <?php if ($edit_data) echo '<input type="hidden" name="id" value="' . $edit_data['id'] . '">'; ?>
    <div class="mb-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hukuman</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" name="jenis_hukuman" value="<?= $edit_data['jenis_hukuman'] ?? '' ?>" required>
    </div>
    <div class="mb-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Lama Hukuman</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" name="lama_hukuman" value="<?= $edit_data['lama_hukuman'] ?? '' ?>">
    </div>
    <div class="mb-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Vonis Putusan</label>
      <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" name="vonis_putusan"><?= $edit_data['vonis_putusan'] ?? '' ?></textarea>
    </div>
    <div class="mb-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" name="status" value="<?= $edit_data['status'] ?? '' ?>">
    </div>
    <button type="submit" name="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
    <?php if ($edit_data) echo '<a href="Inputjenishukuman.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal Edit</a>'; ?>
  </form>

  <hr class="my-6 border-gray-200">
  <h4 class="text-lg font-semibold mb-3">Data Jenis Hukuman</h4>
  <div class="overflow-x-auto">
    <table id="hukumanTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">No</th>
          <th class="px-3 py-2">Jenis Hukuman</th>
          <th class="px-3 py-2">Lama</th>
          <th class="px-3 py-2">Vonis</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
        while ($d = mysqli_fetch_array($data)) {
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2"><?= $d['jenis_hukuman'] ?></td>
            <td class="px-3 py-2"><?= $d['lama_hukuman'] ?></td>
            <td class="px-3 py-2"><?= $d['vonis_putusan'] ?></td>
            <td class="px-3 py-2"><?= $d['status'] ?></td>
            <td class="px-3 py-2">
              <a href="Inputjenishukuman.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <a href="Inputjenishukuman.php?delete=<?= $d['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded-lg transition" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#hukumanTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>
