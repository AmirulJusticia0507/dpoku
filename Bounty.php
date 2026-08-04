<?php 
include 'Koneksi.php'; 
include 'Header.php'; 
include 'Sidebar.php'; 
include 'assets.php'; 
?>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2 d-flex justify-content-between align-items-center">
        <span>Form Input Bounty</span>
        <div>
          <a href="export_dpo.php" class="btn btn-success btn-sm"><i class="fas fa-file-csv"></i> Export CSV DPO</a>
          <a href="audit_log.php" class="btn btn-info btn-sm text-white"><i class="fas fa-history"></i> Audit Log</a>
        </div>
      </h3>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

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
         $stmt->bind_param('iiii', $jumlah_bounty, $id_kasus, $id_hukuman, $createdBy);
         if ($stmt->execute()) {
             $newId = (int) $stmt->insert_id;
             $stmt->close();
             include __DIR__.'/lib/audit_log.php';
             log_audit('create', 'bounty', $newId, "Tambah bounty kasus=$id_kasus hukuman=$id_hukuman");
             echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
         } else {
             $stmt->close();
             echo '<div class="alert alert-danger mt-3">Gagal menyimpan data.</div>';
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
         $stmt->bind_param('iiiii', $jumlah_bounty, $id_kasus, $id_hukuman, $upBy, $id);
         if ($stmt->execute()) {
             $stmt->close();
             include __DIR__.'/lib/audit_log.php';
             log_audit('update', 'bounty', $id, "Update bounty -> kasus=$id_kasus hukuman=$id_hukuman");
             echo '<div class="alert alert-success mt-3">Data berhasil diupdate!</div>';
         } else {
             $stmt->close();
             echo '<div class="alert alert-danger mt-3">Gagal update data.</div>';
         }
      }

      // Proses Hapus Data
      if (isset($_GET['hapus'])) {
         $id = (int) $_GET['hapus'];
         $stmt = $koneksidpogendeng->prepare("DELETE FROM bounty WHERE id=?");
         $stmt->bind_param('i', $id);
         if ($stmt->execute()) {
             $stmt->close();
             include __DIR__.'/lib/audit_log.php';
             log_audit('delete', 'bounty', $id, 'Hapus bounty');
             echo '<div class="alert alert-success mt-3">Data berhasil dihapus!</div>';
         } else {
             $stmt->close();
             echo '<div class="alert alert-danger mt-3">Gagal menghapus data.</div>';
         }
      }

      // Ambil data jika mau edit
      $editData = null;
      if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $result = mysqli_query($koneksidpogendeng, "SELECT * FROM bounty WHERE id='$id'");
        $editData = mysqli_fetch_array($result);
      }
      ?>

      <!-- Form Input / Edit -->
      <form action="" method="POST">
        <?php if ($editData) { ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php } ?>
        <div class="mb-3">
          <label for="jumlah_bounty" class="form-label">Jumlah Bounty</label>
          <input type="text" class="form-control rupiah" id="jumlah_bounty" name="jumlah_bounty" required
            value="<?= $editData ? number_format($editData['jumlah_bounty'], 0, ',', '.') : '' ?>">
        </div>

        <div class="mb-3">
          <label for="id_kasus" class="form-label">Jenis Kasus</label>
          <select class="form-control" id="id_kasus" name="id_kasus" required>
            <option value="">Pilih Kasus</option>
            <?php
            $kasus = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus");
            while ($k = mysqli_fetch_array($kasus)) {
              $selected = $editData && $editData['id_kasus'] == $k['id'] ? 'selected' : '';
              echo "<option value='{$k['id']}' $selected>{$k['jenis_kasus']}</option>";
            }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="id_hukuman" class="form-label">Jenis Hukuman</label>
          <select class="form-control" id="id_hukuman" name="id_hukuman" required>
            <option value="">Pilih Hukuman</option>
            <?php
            $hukuman = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
            while ($h = mysqli_fetch_array($hukuman)) {
              $selected = $editData && $editData['id_hukuman'] == $h['id'] ? 'selected' : '';
              echo "<option value='{$h['id']}' $selected>{$h['jenis_hukuman']}</option>";
            }
            ?>
          </select>
        </div>

        <?php if ($editData) { ?>
          <button type="submit" name="update" class="btn btn-warning">Update</button>
          <a href="Bounty.php" class="btn btn-secondary">Batal</a>
        <?php } else { ?>
          <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
        <?php } ?>
      </form>

      <hr>
      <h4>Data Bounty</h4>
      <table id="bountyTable" class="display table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Jumlah Bounty</th>
            <th>Jenis Kasus</th>
            <th>Jenis Hukuman</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          $data = mysqli_query($koneksidpogendeng, "SELECT bounty.*, jenis_kasus.jenis_kasus, jenis_hukuman.jenis_hukuman 
                                                    FROM bounty 
                                                    LEFT JOIN jenis_kasus ON bounty.id_kasus = jenis_kasus.id
                                                    LEFT JOIN jenis_hukuman ON bounty.id_hukuman = jenis_hukuman.id");
          while ($d = mysqli_fetch_array($data)) {
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>Rp <?= number_format($d['jumlah_bounty'], 0, ',', '.') ?></td>
              <td><?= $d['jenis_kasus'] ?></td>
              <td><?= $d['jenis_hukuman'] ?></td>
              <td>
                <a href="Bounty.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="Bounty.php?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>

    </div>
  </section>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
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
