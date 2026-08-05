<?php
include 'session.php';
include 'Koneksi.php';
$page_title = 'Dashboard DPOKU';
include 'Header.php';

$sql = "SELECT dpo.*, bounty.jumlah_bounty FROM dpo 
        LEFT JOIN bounty ON bounty.id_kasus = dpo.id";
$result = $koneksidpogendeng->query($sql);
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php while ($row = $result->fetch()): ?>
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <img src="uploads/<?= htmlspecialchars($row['foto']); ?>" class="h-64 w-full object-cover" alt="<?= htmlspecialchars($row['nama_lengkap']); ?>">
      <div class="p-4 text-center">
        <h5 class="font-bold text-gray-800"><?= strtoupper(htmlspecialchars($row['nama_lengkap'])); ?></h5>
        <p class="text-gray-500 text-sm mt-1">Bounty: Rp <?= number_format($row['jumlah_bounty']); ?> Juta</p>
        <h6 class="text-red-600 font-bold mt-1">DEAD OR ALIVE</h6>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include 'Footer.php'; ?>
