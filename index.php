<?php
include 'session.php';
include 'Koneksi.php';
$page_title = 'Dashboard DPOKU';
include 'Header.php';

// --- Statistik ---
$totalDpo   = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo")->fetchColumn();
$totalBuron = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo WHERE status_dpo='BURON'")->fetchColumn();
$totalTertangkap = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo WHERE status_dpo='TERTANGKAP'")->fetchColumn();
$totalBounty = (int) $koneksidpogendeng->query("SELECT COALESCE(SUM(jumlah_bounty),0) FROM bounty")->fetchColumn();
$totalKasus = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM jenis_kasus")->fetchColumn();
$totalUser  = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM \"user\"")->fetchColumn();

// Statistik daftar pejabat (BPN / KABINET / DPR)
$totalPejabat = (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM daftar_pejabat")->fetchColumn();
$perSumber    = $koneksidpogendeng->query(
    "SELECT sumber, COUNT(*) AS jumlah FROM daftar_pejabat GROUP BY sumber ORDER BY jumlah DESC"
)->fetchAll();
$topInstansiPejabat = $koneksidpogendeng->query(
    "SELECT instansi, COUNT(*) AS jumlah
     FROM daftar_pejabat WHERE instansi <> '' AND instansi <> 'TIDAK DIKETAHUI'
     GROUP BY instansi ORDER BY jumlah DESC LIMIT 5"
)->fetchAll();

// Kasus teratas
$topKasus = $koneksidpogendeng->query(
    "SELECT jenis_kasus.jenis_kasus, COUNT(dpo.id) AS jumlah
     FROM jenis_kasus
     LEFT JOIN dpo ON dpo.jenis_kasus_id = jenis_kasus.id
     GROUP BY jenis_kasus.id
     ORDER BY jumlah DESC LIMIT 5"
)->fetchAll();

// Daftar DPO (JOIN FK + bounty)
$dpoList = $koneksidpogendeng->query(
    "SELECT dpo.*, instansi.nama_instansi, jenis_kasus.jenis_kasus AS nama_kasus,
            jenis_hukuman.jenis_hukuman AS nama_hukuman,
            COALESCE(bounty.jumlah_bounty, 0) AS jumlah_bounty
     FROM dpo
     LEFT JOIN instansi ON instansi.id = dpo.instansi_id
     LEFT JOIN jenis_kasus ON jenis_kasus.id = dpo.jenis_kasus_id
     LEFT JOIN jenis_hukuman ON jenis_hukuman.id = dpo.jenis_hukuman_id
     LEFT JOIN bounty ON bounty.id_kasus = dpo.jenis_kasus_id
     ORDER BY dpo.created_at DESC"
)->fetchAll();
?>

<!-- ===================== STAT CARDS ===================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl"><i class="fas fa-user-secret"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= $totalDpo ?></p>
      <p class="text-sm text-gray-500">Total DPO</p>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xl"><i class="fas fa-fire"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= $totalBuron ?></p>
      <p class="text-sm text-gray-500">BURON</p>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl"><i class="fas fa-handcuffs"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= $totalTertangkap ?></p>
      <p class="text-sm text-gray-500">TERTANGKAP</p>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 text-xl"><i class="fas fa-money-bill-wave"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800">Rp <?= number_format($totalBounty, 0, ',', '.') ?></p>
      <p class="text-sm text-gray-500">Total Bounty</p>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xl"><i class="fas fa-gavel"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= $totalKasus ?></p>
      <p class="text-sm text-gray-500">Jenis Kasus</p>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-600 text-xl"><i class="fas fa-users"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= $totalUser ?></p>
      <p class="text-sm text-gray-500">User Terdaftar</p>
    </div>
  </div>
</div>

<!-- ===================== DAFTAR PEJABAT ===================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="bg-white rounded-xl shadow p-5 lg:col-span-2">
    <div class="flex flex-wrap justify-between items-center mb-3">
      <h4 class="font-bold text-gray-800">Daftar Pejabat (BPN / KABINET / DPR)</h4>
      <a href="daftar_pejabat.php" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">Lihat semua <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <?php foreach ($perSumber as $ps): ?>
        <a href="daftar_pejabat.php?sumber=<?= urlencode($ps['sumber']) ?>" class="bg-gray-50 hover:bg-gray-100 rounded-xl p-4 text-center transition">
          <p class="text-3xl font-bold text-gray-800"><?= number_format((int) $ps['jumlah'], 0, ',', '.') ?></p>
          <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($ps['sumber']) ?></p>
        </a>
      <?php endforeach; ?>
      <a href="daftar_pejabat.php" class="bg-gray-50 hover:bg-gray-100 rounded-xl p-4 text-center transition">
        <p class="text-3xl font-bold text-gray-800"><?= number_format($totalPejabat, 0, ',', '.') ?></p>
        <p class="text-sm text-gray-500 mt-1">Total</p>
      </a>
    </div>
  </div>
  <div class="bg-white rounded-xl shadow p-5">
    <h4 class="font-bold text-gray-800 mb-3">Instansi Terbanyak</h4>
    <?php foreach ($topInstansiPejabat as $ti): ?>
      <div class="flex justify-between items-center py-2 border-b border-gray-100 text-sm">
        <span class="text-gray-700 truncate pr-2"><?= htmlspecialchars($ti['instansi']) ?></span>
        <span class="font-bold text-gray-800 shrink-0"><?= number_format((int) $ti['jumlah'], 0, ',', '.') ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$topInstansiPejabat): ?>
      <p class="text-sm text-gray-400">Belum ada data.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ===================== KASUS TERATAS ===================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="bg-white rounded-xl shadow p-5">
    <h4 class="font-bold text-gray-800 mb-3">Kasus Terbanyak</h4>
    <?php foreach ($topKasus as $tk): ?>
      <div class="flex justify-between items-center py-2 border-b border-gray-100 text-sm">
        <span class="text-gray-700"><?= htmlspecialchars($tk['jenis_kasus']) ?></span>
        <span class="font-bold text-gray-800"><?= (int) $tk['jumlah'] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="bg-white rounded-xl shadow p-5 lg:col-span-2">
    <h4 class="font-bold text-gray-800 mb-3">Ringkasan Status</h4>
    <div class="space-y-3">
      <div>
        <div class="flex justify-between text-sm mb-1"><span class="text-gray-600">BURON</span><span class="font-bold"><?= $totalBuron ?> (<?= $totalDpo ? round($totalBuron / $totalDpo * 100) : 0 ?>%)</span></div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-red-600 rounded-full" style="width: <?= $totalDpo ? round($totalBuron / $totalDpo * 100) : 0 ?>%"></div></div>
      </div>
      <div>
        <div class="flex justify-between text-sm mb-1"><span class="text-gray-600">TERTANGKAP</span><span class="font-bold"><?= $totalTertangkap ?> (<?= $totalDpo ? round($totalTertangkap / $totalDpo * 100) : 0 ?>%)</span></div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-green-600 rounded-full" style="width: <?= $totalDpo ? round($totalTertangkap / $totalDpo * 100) : 0 ?>%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- ===================== DAFTAR DPO ===================== -->
<div class="bg-white rounded-xl shadow p-6">
  <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
    <h3 class="text-xl font-bold text-gray-800">Daftar DPO</h3>
    <a href="Inputdpo.php" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
      <i class="fas fa-plus mr-1"></i> Tambah DPO
    </a>
  </div>

  <div class="overflow-x-auto">
    <table id="dpoTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">Foto</th>
          <th class="px-3 py-2">Nama</th>
          <th class="px-3 py-2">NIK</th>
          <th class="px-3 py-2">Kasus</th>
          <th class="px-3 py-2">Hukuman</th>
          <th class="px-3 py-2">Instansi</th>
          <th class="px-3 py-2">Bounty</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if (count($dpoList) > 0): ?>
          <?php foreach ($dpoList as $row): $badge = match($row['status_dpo']) { 'BURON' => 'bg-red-600', 'TERTANGKAP' => 'bg-green-600', 'MENINGGAL DUNIA' => 'bg-gray-500', default => 'bg-gray-500' }; ?>
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2"><img src="<?= htmlspecialchars($row['foto']) ?>" class="h-12 w-12 object-cover rounded-full" alt="foto"></td>
              <td class="px-3 py-2 font-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($row['nik']) ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($row['nama_kasus'] ?? '-') ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($row['nama_hukuman'] ?? '-') ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($row['nama_instansi'] ?? '-') ?></td>
              <td class="px-3 py-2 text-green-700 font-semibold">Rp <?= number_format($row['jumlah_bounty'], 0, ',', '.') ?></td>
              <td class="px-3 py-2"><span class="inline-block <?= $badge ?> text-white text-xs font-bold px-3 py-1 rounded-full"><?= htmlspecialchars($row['status_dpo']) ?></span></td>
              <td class="px-3 py-2 whitespace-nowrap">
                <a href="detail_dpo.php?id=<?= $row['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg transition" title="Detail"><i class="fas fa-eye"></i></a>
                <a href="edit_dpo.php?id=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-1 rounded-lg transition" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="hapus_dpo.php?id=<?= $row['id'] ?>" class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1 rounded-lg transition" title="Hapus" onclick="return confirm('Yakin mau hapus DPO ini?')"><i class="fas fa-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="9" class="px-3 py-4 text-center text-gray-400">Belum ada data DPO.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function () {
    $('#dpoTable').DataTable({
      order: [[1, 'asc']],
      language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
    });
  });
</script>

<?php include 'Footer.php'; ?>
