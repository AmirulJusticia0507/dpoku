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
      <h3 class="mb-2">Form Input Jenis Kasus</h3>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php
      // Proses Tambah Data
      if (isset($_POST['submit'])) {
        $jenis_kasus = $_POST['jenis_kasus'];
        $deskripsi_kasus = $_POST['deskripsi_kasus'];

        $query = "INSERT INTO jenis_kasus (jenis_kasus, deskripsi_kasus) VALUES ('$jenis_kasus', '$deskripsi_kasus')";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal menyimpan data.</div>';
        }
      }

      // Proses Update Data
      if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $jenis_kasus = $_POST['jenis_kasus'];
        $deskripsi_kasus = $_POST['deskripsi_kasus'];

        $query = "UPDATE jenis_kasus SET jenis_kasus='$jenis_kasus', deskripsi_kasus='$deskripsi_kasus' WHERE id='$id'";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil diupdate!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal update data.</div>';
        }
      }

      // Proses Hapus Data
      if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $query = "DELETE FROM jenis_kasus WHERE id='$id'";
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
        $result = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus WHERE id='$id'");
        $editData = mysqli_fetch_array($result);
      }
      ?>

      <!-- Form Input / Edit -->
      <form action="" method="POST">
        <?php if ($editData) { ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php } ?>
        <div class="mb-3">
          <label for="jenis_kasus" class="form-label">Jenis Kasus</label>
          <input type="text" class="form-control" id="jenis_kasus" name="jenis_kasus" required
                 value="<?= $editData ? $editData['jenis_kasus'] : '' ?>">
        </div>
        <div class="mb-3">
          <label for="deskripsi_kasus" class="form-label">Deskripsi Kasus</label>
          <textarea class="form-control" id="deskripsi_kasus" name="deskripsi_kasus" rows="4"><?= $editData ? $editData['deskripsi_kasus'] : '' ?></textarea>
        </div>
        <?php if ($editData) { ?>
          <button type="submit" name="update" class="btn btn-warning">Update</button>
          <a href="Inputjeniskasus.php" class="btn btn-secondary">Batal</a>
        <?php } else { ?>
          <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
        <?php } ?>
      </form>

      <hr>
      <h4>Data Jenis Kasus</h4>
      <table id="kasusTable" class="display table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Jenis Kasus</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          $data = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus");
          while ($d = mysqli_fetch_array($data)) {
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= $d['jenis_kasus'] ?></td>
              <td><?= $d['deskripsi_kasus'] ?></td>
              <td>
                <a href="Inputjeniskasus.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="Inputjeniskasus.php?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
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
    $('#kasusTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>
