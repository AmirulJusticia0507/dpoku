<?php include 'Koneksi.php'; ?>
<?php include 'Header.php'; ?>
<?php include 'Sidebar.php'; ?>
<?php include 'assets.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<!-- Content Wrapper -->
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Form Input Instansi</h3>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php
      // Proses Tambah Data
      if (isset($_POST['submit'])) {
        $nama_instansi = $_POST['nama_instansi'];
        $keterangan_instansi = $_POST['keterangan_instansi'];

        $query = "INSERT INTO instansi (nama_instansi, keterangan_instansi) VALUES ('$nama_instansi', '$keterangan_instansi')";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal menyimpan data.</div>';
        }
      }

      // Proses Update Data
      if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama_instansi = $_POST['nama_instansi'];
        $keterangan_instansi = $_POST['keterangan_instansi'];

        $query = "UPDATE instansi SET nama_instansi='$nama_instansi', keterangan_instansi='$keterangan_instansi' WHERE id='$id'";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil diupdate!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal update data.</div>';
        }
      }

      // Proses Hapus Data
      if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $query = "DELETE FROM instansi WHERE id='$id'";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil dihapus!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal menghapus data.</div>';
        }
      }

      // Ambil data jika mau edit
      $editData = null;
      if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $result = mysqli_query($koneksidpogendeng, "SELECT * FROM instansi WHERE id='$id'");
        $editData = mysqli_fetch_array($result);
      }
      ?>

      <!-- Form Input / Edit -->
      <form action="" method="POST">
        <?php if ($editData) { ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php } ?>
        <div class="mb-3">
          <label for="nama_instansi" class="form-label">Nama Instansi</label>
          <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" required
                 value="<?= $editData ? $editData['nama_instansi'] : '' ?>">
        </div>
        <div class="mb-3">
          <label for="keterangan_instansi" class="form-label">Keterangan Instansi</label>
          <textarea class="form-control" id="keterangan_instansi" name="keterangan_instansi" rows="4"><?= $editData ? $editData['keterangan_instansi'] : '' ?></textarea>
        </div>
        <?php if ($editData) { ?>
          <button type="submit" name="update" class="btn btn-warning">Update</button>
          <a href="Inputinstansi.php" class="btn btn-secondary">Batal</a>
        <?php } else { ?>
          <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
        <?php } ?>
      </form>

      <hr>
      <h4>Data Instansi</h4>
      <table id="instansiTable" class="display table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Instansi</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          $data = mysqli_query($koneksidpogendeng, "SELECT * FROM instansi");
          while ($d = mysqli_fetch_array($data)) {
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= $d['nama_instansi'] ?></td>
              <td><?= $d['keterangan_instansi'] ?></td>
              <td>
                <a href="Inputinstansi.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="Inputinstansi.php?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>

    </div>
  </section>
</div>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function() {
    $('#instansiTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>

