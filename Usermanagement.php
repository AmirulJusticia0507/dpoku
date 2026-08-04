<?php include 'Koneksi.php'; ?>
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
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = $_POST['fullname'];
        $jumlah_saldo_bounty = $_POST['jumlah_saldo_bounty'];
        $amount_saldo = $_POST['amount_saldo'];
        $email = $_POST['email'];

        $query = "INSERT INTO user (username, password, fullname, jumlah_saldo_bounty, amount_saldo, email) 
                  VALUES ('$username', '$password', '$fullname', '$jumlah_saldo_bounty', '$amount_saldo', '$email')";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil disimpan!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal menyimpan data.</div>';
        }
      }

      // Proses Update Data
      if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $jumlah_saldo_bounty = $_POST['jumlah_saldo_bounty'];
        $amount_saldo = $_POST['amount_saldo'];
        $email = $_POST['email'];

        $query = "UPDATE user SET username='$username', fullname='$fullname', 
                  jumlah_saldo_bounty='$jumlah_saldo_bounty', amount_saldo='$amount_saldo', email='$email' 
                  WHERE id='$id'";
        if (mysqli_query($koneksidpogendeng, $query)) {
          echo '<div class="alert alert-success mt-3">Data berhasil diupdate!</div>';
        } else {
          echo '<div class="alert alert-danger mt-3">Gagal update data.</div>';
        }
      }

      // Proses Hapus Data
      if (isset($_GET['hapus'])) {
        $id = $_GET['hapus'];
        $query = "DELETE FROM user WHERE id='$id'";
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
        $result = mysqli_query($koneksidpogendeng, "SELECT * FROM user WHERE id='$id'");
        $editData = mysqli_fetch_array($result);
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
          $data = mysqli_query($koneksidpogendeng, "SELECT * FROM user");
          while ($d = mysqli_fetch_array($data)) {
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= $d['username'] ?></td>
              <td><?= $d['fullname'] ?></td>
              <td><?= 'Rp ' . number_format($d['jumlah_saldo_bounty'], 0, ',', '.'); ?></td>
              <td><?= 'Rp ' . number_format($d['amount_saldo'], 0, ',', '.'); ?></td>
              <td><?= $d['email'] ?></td>
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
  });
</script>
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

<script>
  $(document).ready(function() {
    $('#userTable').DataTable();
  });
</script>

<?php include 'Footer.php'; ?>
