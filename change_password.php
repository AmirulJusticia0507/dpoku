<?php
// change_password.php - Ubah password akun sendiri
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$msg    = '';
$msgType = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($oldPass === '' || $newPass === '' || $confirm === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($newPass !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (strlen($newPass) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } else {
        $stmt = $koneksidpogendeng->prepare('SELECT password FROM "user" WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($oldPass, $user['password'])) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $up = $koneksidpogendeng->prepare('UPDATE "user" SET password = ? WHERE id = ?');
            $up->execute([$hash, $_SESSION['user_id']]);

            include __DIR__.'/lib/audit_log.php';
            log_audit('update', 'user', $_SESSION['user_id'], 'Ganti password sendiri');

            $msg = 'Password berhasil diubah.';
            $msgType = 'success';
        } else {
            $error = 'Password lama salah.';
        }
    }
}

$page_title = 'Ganti Password';
include 'Header.php';
?>
<div class="bg-white rounded-xl shadow p-6 max-w-lg">
  <h3 class="text-xl font-bold text-gray-800 mb-4"><i class="fas fa-key mr-2 text-gray-500"></i>Ganti Password</h3>

  <?php if ($error): ?>
    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($msg): ?>
    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
      <input type="password" name="old_password" required
        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
      <input type="password" name="new_password" required minlength="6"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
      <input type="password" name="confirm_password" required minlength="6"
        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <button type="submit"
      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition">
      <i class="fas fa-save mr-1"></i> Simpan Password
    </button>
  </form>
</div>
<?php include 'Footer.php'; ?>
