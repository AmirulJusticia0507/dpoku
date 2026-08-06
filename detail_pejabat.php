<?php
// detail_pejabat.php - lihat detail satu pejabat
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Detail Pejabat';
include 'Header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $koneksidpogendeng->prepare('SELECT * FROM daftar_pejabat WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
?>

<div class="bg-white rounded-xl shadow p-6 max-w-3xl">
  <div class="flex flex-wrap justify-between items-center mb-5 gap-2">
    <h3 class="text-xl font-bold text-gray-800">Detail Pejabat</h3>
    <a href="daftar_pejabat.php" class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
      <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
  </div>

  <?php if (!$row): ?>
    <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">Data pejabat tidak ditemukan.</div>
  <?php else: ?>
    <dl class="divide-y divide-gray-200 text-sm">
      <?php
      $fields = [
        'Nama'        => 'nama',
        'Jabatan'     => 'jabatan',
        'Instansi'    => 'instansi',
        'Sumber'      => 'sumber',
        'Keterangan'  => 'keterangan',
        'Dibuat pada' => 'created_at',
      ];
      foreach ($fields as $label => $col):
        $val = $row[$col];
        if ($col === 'created_at') $val = $val ? date('d M Y H:i', strtotime($val)) : '-';
        if ($val === '' || $val === null) $val = '-';
      ?>
        <div class="py-3 grid grid-cols-1 sm:grid-cols-4 gap-1">
          <dt class="text-gray-500 font-medium sm:col-span-1"><?= $label ?></dt>
          <dd class="text-gray-800 sm:col-span-3"><?= htmlspecialchars($val) ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
  <?php endif; ?>
</div>

<?php include 'Footer.php'; ?>
