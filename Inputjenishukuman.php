<?php include 'Koneksi.php'; ?>
<?php include 'Header.php'; ?>
<?php include 'Sidebar.php'; ?>
<?php include 'assets.php'; ?>

<!-- Cek Proses Edit -->
<?php
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
    echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
  } else {
    echo '<div class="alert alert-danger mt-3">Gagal menyimpan data.</div>';
  }
}
?>

<!-- Form Input/Edit -->
<div class="content-wrapper">
  <div class="content-header">
    <h3>Form Input Jenis Hukuman</h3>
  </div>
  <section class="content">
    <form method="POST">
      <?php if ($edit_data) echo '<input type="hidden" name="id" value="' . $edit_data['id'] . '">'; ?>
      <div class="mb-3">
        <label>Jenis Hukuman</label>
        <input type="text" class="form-control" name="jenis_hukuman" value="<?= $edit_data['jenis_hukuman'] ?? '' ?>" required>
      </div>
      <div class="mb-3">
        <label>Lama Hukuman</label>
        <input type="text" class="form-control" name="lama_hukuman" value="<?= $edit_data['lama_hukuman'] ?? '' ?>">
      </div>
      <div class="mb-3">
        <label>Vonis Putusan</label>
        <textarea class="form-control" name="vonis_putusan"><?= $edit_data['vonis_putusan'] ?? '' ?></textarea>
      </div>
      <div class="mb-3">
        <label>Status</label>
        <input type="text" class="form-control" name="status" value="<?= $edit_data['status'] ?? '' ?>">
      </div>
      <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
      <?php if ($edit_data) echo '<a href="Inputjenishukuman.php" class="btn btn-secondary">Batal Edit</a>'; ?>
    </form>

    <hr>
    <h4>Data Jenis Hukuman</h4>
    <table id="hukumanTable" class="display table table-bordered">
      <thead>
        <tr>
          <th>No</th>
          <th>Jenis Hukuman</th>
          <th>Lama</th>
          <th>Vonis</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        $data = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
        while ($d = mysqli_fetch_array($data)) {
        ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= $d['jenis_hukuman'] ?></td>
            <td><?= $d['lama_hukuman'] ?></td>
            <td><?= $d['vonis_putusan'] ?></td>
            <td><?= $d['status'] ?></td>
            <td>
              <a href="Inputjenishukuman.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="Inputjenishukuman.php?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </section>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>$(document).ready(function() { $('#hukumanTable').DataTable(); });</script>
<?php include 'Footer.php'; ?>
