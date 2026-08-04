<?php include 'Koneksi.php';
include __DIR__.'/lib/audit_log.php';

// Proteksi: hanya user login yang boleh kelola user
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include 'Header.php'; ?>
<?php include 'Sidebar.php'; ?>
<?php include 'assets.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


<!-- Content Wrapper -->
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Form Input User</h3>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php
      // Proses Tambah Data
      if (isset($_POST['submit'])) {
        $username = mysqli_real_escape_string($koneksidpogendeng, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = mysqli_real_escape_string($koneksidpogendeng, $_POST['fullname']);
        $jumlah_saldo_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_saldo_bounty'] ?? '0');
        $amount_saldo = preg_replace('/[^0-9]/', '', $_POST['amount_saldo'] ?? '0');
        $email = mysqli_real_escape_string($koneksidpogendeng, $_POST['email']);
        $createdBy = (int) ($_SESSION['user_id'] ?? 0);

        $stmt = $koneksidpogendeng->prepare(
            "INSERT INTO user (username, password, fullname, jumlah_saldo_bounty, amount_saldo, email, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssi', $username, $password, $fullname, $jumlah_saldo_bounty, $amount_saldo, $email, $createdBy);
        if ($stmt->execute()) {
            $newId = (int) $stmt->insert_id;
            $stmt->close();
            log_audit('create', 'user', $newId, "Tambah user $username");
            echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
        } else {
            $stmt->close();
            echo '<div class="alert alert-danger mt-3">Gagal menyimpan data: '.$koneksidpogendeng->error.'</div>';
        }
      }

      // Proses Update Data
      if (isset($_POST['update'])) {
        $id = (int) $_POST['id'];
        $username = mysqli_real_escape_string($koneksidpogendeng, $_POST['username']);
        $fullname = mysqli_real_escape_string($koneksidpogendeng, $_POST['fullname']);
        $jumlah_saldo_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_saldo_bounty'] ?? '0');
        $amount_saldo = preg_replace('/[^0-9]/', '', $_POST['amount_saldo'] ?? '0');
        $email = mysqli_real_escape_string($koneksidpogendeng, $_POST['email']);
        $updatedBy = (int) ($_SESSION['user_id'] ?? 0);

        $stmt = $koneksidpogendeng->prepare(
            "UPDATE user SET username=?, fullname=?, jumlah_saldo_bounty=?, amount_saldo=?, email=?, updated_by=? WHERE id=?");
        $stmt->bind_param('ssssiii', $username, $fullname, $jumlah_saldo_bounty, $amount_saldo, $email, $updatedBy, $id);
        if ($stmt->execute()) {
            $stmt->close();
            log_audit('update', 'user', $id, "Update user $username");
            echo '<div class="alert alert-success mt-3">Data berhasil diupdate!</div>';
        } else {
            $stmt->close();
            echo '<div class="alert alert-danger mt-3">Gagal update data: '.$koneksidpogendeng->error.'</div>';
        }
      }

      // Proses Hapus Data
      if (isset($_GET['hapus'])) {
        $id = (int) $_GET['hapus'];
        $stmt = $koneksidpogendeng->prepare("DELETE FROM user WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            log_audit('delete', 'user', $id, 'Hapus user');
            echo '<div class="alert alert-success mt-3">Data berhasil dihapus!</div>';
        } else {
            $stmt->close();
            echo '<div class="alert alert-danger mt-3">Gagal menghapus data.</div>';
        }
      }

      // Ambil data jika mau edit
      $editData = null;
      if (isset($_GET['edit'])) {
        $id = (int) $_GET['edit'];
        $stmt = $koneksidpogendeng->prepare("SELECT * FROM user WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $editData = $result->fetch_assoc();
        $stmt->close();
      }
      ?>

      <!-- Form Input / Edit -->
      <form action="" method="POST">
        <?php if ($editData) { ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php } ?>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" required value="<?= $editData ? $editData['username'] : '' ?>">
        </div>
        <?php if (!$editData) { ?>
        <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" required>
                <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                </button>
            </div>
        </div>
        <?php } ?>
        <div class="mb-3">
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullname" name="fullname" value="<?= $editData ? $editData['fullname'] : '' ?>">
        </div>
        <div class="mb-3">
        <label for="jumlah_saldo_bounty" class="form-label">Saldo Bounty</label>
            <input type="text" class="form-control rupiah" id="jumlah_saldo_bounty" name="jumlah_saldo_bounty" value="<?= $editData ? number_format($editData['jumlah_saldo_bounty'], 0, ',', '.') : '0' ?>">
        </div>

        <div class="mb-3">
        <label for="amount_saldo" class="form-label">Amount Saldo</label>
            <input type="text" class="form-control rupiah" id="amount_saldo" name="amount_saldo" value="<?= $editData ? number_format($editData['amount_saldo'], 0, ',', '.') : '0' ?>">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= $editData ? $editData['email'] : '' ?>">
        </div>

        <?php if ($editData) { ?>
          <button type="submit" name="update" class="btn btn-warning">Update</button>
          <a href="Usermanagement.php" class="btn btn-secondary">Batal</a>
        <?php } else { ?>
          <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
        <?php } ?>
      </form>

      <hr>
      <h4>Data User</h4>
      <table id="userTable" class="display table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Username</th>
            <th>Fullname</th>
            <th>Saldo Bounty</th>
            <th>Amount Saldo</th>
            <th>Email</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          $data = mysqli_query($koneksidpogendeng, "SELECT * FROM user ORDER BY id DESC");
          while ($d = mysqli_fetch_array($data)) {
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($d['username']) ?></td>
              <td><?= htmlspecialchars($d['fullname']) ?></td>
              <td><?= 'Rp ' . number_format($d['jumlah_saldo_bounty'], 0, ',', '.') ?></td>
              <td><?= 'Rp ' . number_format($d['amount_saldo'], 0, ',', '.') ?></td>
              <td><?= htmlspecialchars($d['email']) ?></td>
              <td>
                <a href="Usermanagement.php?edit=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="Usermanagement.php?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
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
    $('#userTable').DataTable();

    // Format Rupiah saat ketik
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

      rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      $(this).val('Rp ' + rupiah);
    });

    // Toggle show/hide password
    $('.toggle-password').on('click', function() {
      const passwordInput = $('#password');
      const icon = $('#toggleIcon');
      if (passwordInput.attr('type') === 'password') {
        passwordInput.attr('type', 'text');
        icon.removeClass('bi-eye-slash').addClass('bi-eye');
      } else {
        passwordInput.attr('type', 'password');
        icon.removeClass('bi-eye').addClass('bi-eye-slash');
      }
    });
  });
</script>

<?php include 'Footer.php'; ?>
