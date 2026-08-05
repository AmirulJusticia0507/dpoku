<?php
include 'Koneksi.php';
include __DIR__.'/lib/audit_log.php';

// Proteksi: hanya user login yang boleh kelola user
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = 'Manajemen User';
include 'Header.php';
?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Form Input User</h3>

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
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
    } else {
        $stmt->close();
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data: '.$koneksidpogendeng->error.'</div>';
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
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil diupdate!</div>';
    } else {
        $stmt->close();
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal update data: '.$koneksidpogendeng->error.'</div>';
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
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil dihapus!</div>';
    } else {
        $stmt->close();
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menghapus data.</div>';
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
  <form action="" method="POST" class="max-w-2xl">
    <?php if ($editData) { ?>
      <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <?php } ?>
    <div class="mb-3">
      <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="username" name="username" required value="<?= $editData ? $editData['username'] : '' ?>">
    </div>
    <?php if (!$editData) { ?>
    <div class="mb-3">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <div class="flex">
            <input type="password" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="password" name="password" required>
            <button class="px-3 border border-l-0 border-gray-300 rounded-r-lg bg-gray-100 hover:bg-gray-200 toggle-password" type="button" tabindex="-1">
            <i class="bi bi-eye-slash" id="toggleIcon"></i>
            </button>
        </div>
    </div>
    <?php } ?>
    <div class="mb-3">
      <label for="fullname" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="fullname" name="fullname" value="<?= $editData ? $editData['fullname'] : '' ?>">
    </div>
    <div class="mb-3">
      <label for="jumlah_saldo_bounty" class="block text-sm font-medium text-gray-700 mb-1">Saldo Bounty</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none rupiah" id="jumlah_saldo_bounty" name="jumlah_saldo_bounty" value="<?= $editData ? number_format($editData['jumlah_saldo_bounty'], 0, ',', '.') : '0' ?>">
    </div>
    <div class="mb-3">
      <label for="amount_saldo" class="block text-sm font-medium text-gray-700 mb-1">Amount Saldo</label>
      <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none rupiah" id="amount_saldo" name="amount_saldo" value="<?= $editData ? number_format($editData['amount_saldo'], 0, ',', '.') : '0' ?>">
    </div>
    <div class="mb-3">
      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" id="email" name="email" value="<?= $editData ? $editData['email'] : '' ?>">
    </div>

    <?php if ($editData) { ?>
      <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition">Update</button>
      <a href="Usermanagement.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition">Batal</a>
    <?php } else { ?>
      <button type="submit" name="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
    <?php } ?>
  </form>

  <hr class="my-6 border-gray-200">
  <h4 class="text-lg font-semibold mb-3">Data User</h4>
  <div class="overflow-x-auto">
    <table id="userTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">No</th>
          <th class="px-3 py-2">Username</th>
          <th class="px-3 py-2">Fullname</th>
          <th class="px-3 py-2">Saldo Bounty</th>
          <th class="px-3 py-2">Amount Saldo</th>
          <th class="px-3 py-2">Email</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = mysqli_query($koneksidpogendeng, "SELECT * FROM user ORDER BY id DESC");
        while ($d = mysqli_fetch_array($data)) {
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['username']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['fullname']) ?></td>
            <td class="px-3 py-2"><?= 'Rp ' . number_format($d['jumlah_saldo_bounty'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= 'Rp ' . number_format($d['amount_saldo'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['email']) ?></td>
            <td class="px-3 py-2">
              <a href="Usermanagement.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <a href="Usermanagement.php?hapus=<?= $d['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1 rounded-lg transition" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

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
