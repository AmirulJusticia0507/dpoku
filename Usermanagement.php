<?php
include 'Koneksi.php';
include __DIR__.'/lib/audit_log.php';

// Proteksi: hanya user login dengan role admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    echo "<script>alert('Akses ditolak. Halaman ini khusus admin.');window.location='index.php';</script>";
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
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = trim($_POST['fullname']);
    $jumlah_saldo_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_saldo_bounty'] ?? '0');
    $amount_saldo = preg_replace('/[^0-9]/', '', $_POST['amount_saldo'] ?? '0');
    $email = trim($_POST['email']);
    $role = trim($_POST['role'] ?? 'operator');
    if (!in_array($role, ['admin', 'operator', 'viewer'])) $role = 'operator';
    $createdBy = (int) ($_SESSION['user_id'] ?? 0);

    $stmt = $koneksidpogendeng->prepare(
        "INSERT INTO \"user\" (username, password, fullname, jumlah_saldo_bounty, amount_saldo, email, role, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $password, $fullname, $jumlah_saldo_bounty, $amount_saldo, $email, $role, $createdBy])) {
        $newId = (int) $koneksidpogendeng->lastInsertId();
        log_audit('create', 'user', $newId, "Tambah user $username");
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil disimpan!</div>';
    } else {
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menyimpan data: '.implode(' ', $koneksidpogendeng->errorInfo()).'</div>';
    }
  }

  // Proses Update Data
  if (isset($_POST['update'])) {
    $id = (int) $_POST['id'];
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $jumlah_saldo_bounty = preg_replace('/[^0-9]/', '', $_POST['jumlah_saldo_bounty'] ?? '0');
    $amount_saldo = preg_replace('/[^0-9]/', '', $_POST['amount_saldo'] ?? '0');
    $email = trim($_POST['email']);
    $role = trim($_POST['role'] ?? 'operator');
    if (!in_array($role, ['admin', 'operator', 'viewer'])) $role = 'operator';
    $updatedBy = (int) ($_SESSION['user_id'] ?? 0);

    $stmt = $koneksidpogendeng->prepare(
        "UPDATE \"user\" SET username=?, fullname=?, jumlah_saldo_bounty=?, amount_saldo=?, email=?, role=?, updated_by=? WHERE id=?");
    if ($stmt->execute([$username, $fullname, $jumlah_saldo_bounty, $amount_saldo, $email, $updatedBy, $id])) {
        log_audit('update', 'user', $id, "Update user $username");
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil diupdate!</div>';
    } else {
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal update data: '.implode(' ', $koneksidpogendeng->errorInfo()).'</div>';
    }
  }

  // Proses Hapus Data
  if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $koneksidpogendeng->prepare("DELETE FROM \"user\" WHERE id=?");
    if ($stmt->execute([$id])) {
        log_audit('delete', 'user', $id, 'Hapus user');
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Data berhasil dihapus!</div>';
    } else {
        echo '<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg mt-3">Gagal menghapus data.</div>';
    }
  }

  // Proses Buka Kunci Akun
  if (isset($_GET['unlock'])) {
    $id = (int) $_GET['unlock'];
    $stmt = $koneksidpogendeng->prepare('UPDATE "user" SET failed_attempts = 0, locked_until = NULL WHERE id=?');
    if ($stmt->execute([$id])) {
        log_audit('update', 'user', $id, 'Buka kunci akun');
        echo '<div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg mt-3">Akun berhasil dibuka kuncinya!</div>';
    }
  }

  // Ambil data jika mau edit
  $editData = null;
  if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $koneksidpogendeng->prepare("SELECT * FROM \"user\" WHERE id=?");
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
    <div class="mb-3">
      <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
      <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <option value="operator" <?= $editData && $editData['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
        <option value="viewer" <?= $editData && $editData['role'] === 'viewer' ? 'selected' : '' ?>>Viewer (read-only)</option>
        <option value="admin" <?= $editData && $editData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
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
          <th class="px-3 py-2">Role</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php
        $no = 1;
        $data = $koneksidpogendeng->query("SELECT * FROM \"user\" ORDER BY id DESC");
        while ($d = $data->fetch()) {
            $isLocked = $d['locked_until'] && strtotime($d['locked_until']) > time();
        ?>
          <tr>
            <td class="px-3 py-2"><?= $no++ ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['username']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['fullname']) ?></td>
            <td class="px-3 py-2"><?= 'Rp ' . number_format($d['jumlah_saldo_bounty'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= 'Rp ' . number_format($d['amount_saldo'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($d['email']) ?></td>
            <td class="px-3 py-2">
              <?php if (($d['role'] ?? 'operator') === 'admin'): ?>
                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded-full">ADMIN</span>
              <?php elseif (($d['role'] ?? 'operator') === 'viewer'): ?>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">VIEWER</span>
              <?php else: ?>
                <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded-full">OPERATOR</span>
              <?php endif; ?>
            </td>
            <td class="px-3 py-2">
              <?php if ($isLocked): ?>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">TERKUNCI</span>
              <?php else: ?>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">AKTIF</span>
              <?php endif; ?>
            </td>
            <td class="px-3 py-2">
              <a href="Usermanagement.php?edit=<?= $d['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Edit</a>
              <?php if ($isLocked): ?>
                <a href="Usermanagement.php?unlock=<?= $d['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-3 py-1 rounded-lg transition">Unlock</a>
              <?php endif; ?>
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
