<?php
$page_title = 'Detail DPO';
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Ambil data DPO lengkap (JOIN FK)
$stmt = $koneksidpogendeng->prepare(
    "SELECT dpo.*, instansi.nama_instansi, jenis_kasus.jenis_kasus AS nama_kasus,
            jenis_hukuman.jenis_hukuman AS nama_hukuman, jenis_hukuman.lama_hukuman,
            COALESCE(bounty.jumlah_bounty, 0) AS jumlah_bounty
     FROM dpo
     LEFT JOIN instansi ON instansi.id = dpo.instansi_id
     LEFT JOIN jenis_kasus ON jenis_kasus.id = dpo.jenis_kasus_id
     LEFT JOIN jenis_hukuman ON jenis_hukuman.id = dpo.jenis_hukuman_id
     LEFT JOIN bounty ON bounty.id_kasus = dpo.jenis_kasus_id
     WHERE dpo.id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    die("Data DPO tidak ditemukan.");
}

// Barang bukti
$buktiStmt = $koneksidpogendeng->prepare("SELECT * FROM barang_bukti WHERE dpo_id = ? ORDER BY id DESC");
$buktiStmt->execute([$id]);
$buktis = $buktiStmt->fetchAll();

// Riwayat status
$logStmt = $koneksidpogendeng->prepare("SELECT * FROM dpo_status_log WHERE dpo_id = ? ORDER BY id DESC");
$logStmt->execute([$id]);
$statusLogs = $logStmt->fetchAll();

$statusBadge = [
    'BURON' => 'bg-red-600',
    'TERTANGKAP' => 'bg-green-600',
    'MENINGGAL DUNIA' => 'bg-gray-500',
];
$badge = $statusBadge[$d['status_dpo']] ?? 'bg-gray-500';

include 'Header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Foto -->
  <div class="bg-white rounded-xl shadow p-6">
    <img src="<?= htmlspecialchars($d['foto']) ?>" alt="Foto DPO" class="w-full h-80 object-cover rounded-lg">
    <h5 class="font-bold text-center text-lg mt-4 text-gray-800"><?= htmlspecialchars($d['nama_lengkap']) ?></h5>
    <p class="text-center text-sm text-gray-500">NIK: <?= htmlspecialchars($d['nik']) ?></p>
    <div class="text-center mt-3">
      <span class="inline-block <?= $badge ?> text-white text-xs font-bold px-3 py-1 rounded-full"><?= htmlspecialchars($d['status_dpo']) ?></span>
    </div>

    <div class="mt-6 space-y-2">
      <a href="download_framed.php?id=<?= $d['id'] ?>" class="block bg-red-600 hover:bg-red-700 text-white text-center font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-image mr-1"></i> Download Foto Framed
      </a>
      <a href="download_pdf.php?id=<?= $d['id'] ?>" class="block bg-yellow-500 hover:bg-yellow-600 text-white text-center font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-file-pdf mr-1"></i> Download Poster PDF
      </a>
      <a href="edit_dpo.php?id=<?= $d['id'] ?>" class="block bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-edit mr-1"></i> Edit DPO
      </a>
      <a href="index.php" class="block bg-gray-500 hover:bg-gray-600 text-white text-center font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
      </a>
    </div>
  </div>

  <!-- Informasi -->
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white rounded-xl shadow p-6">
      <h4 class="text-lg font-bold mb-4 text-gray-800">Data Identitas</h4>
      <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div><dt class="text-gray-500">NIK</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['nik']) ?></dd></div>
        <div><dt class="text-gray-500">Nama Lengkap</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['nama_lengkap']) ?></dd></div>
        <div><dt class="text-gray-500">Tanggal Lahir</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['tanggal_lahir']) ?></dd></div>
        <div><dt class="text-gray-500">Jenis Kelamin</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['jenis_kelamin']) ?></dd></div>
        <div><dt class="text-gray-500">Instansi</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['nama_instansi'] ?? '-') ?></dd></div>
        <div><dt class="text-gray-500">Jenis Kasus</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['nama_kasus'] ?? '-') ?></dd></div>
        <div><dt class="text-gray-500">Jenis Hukuman</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['nama_hukuman'] ?? '-') ?> <?= $d['lama_hukuman'] ? '(' . htmlspecialchars($d['lama_hukuman']) . ')' : '' ?></dd></div>
        <div><dt class="text-gray-500">Jumlah Bounty</dt><dd class="font-semibold text-green-700">Rp <?= number_format($d['jumlah_bounty'], 0, ',', '.') ?></dd></div>
        <div><dt class="text-gray-500">No HP</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['no_hp']) ?></dd></div>
        <div><dt class="text-gray-500">Email</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['email']) ?></dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Media Sosial</dt><dd class="font-semibold text-gray-800"><?= htmlspecialchars($d['media_sosial']) ?></dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Alamat</dt><dd class="font-semibold text-gray-800"><?= nl2br(htmlspecialchars($d['alamat'])) ?></dd></div>
      </dl>
    </div>

    <!-- Barang Bukti -->
    <div class="bg-white rounded-xl shadow p-6">
      <h4 class="text-lg font-bold mb-4 text-gray-800">Barang Bukti</h4>

      <form action="proses_bukti.php" method="POST" enctype="multipart/form-data" class="bg-gray-50 rounded-lg p-4 mb-4">
        <input type="hidden" name="dpo_id" value="<?= $d['id'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <input type="file" name="bukti[]" class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm" multiple required>
          <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="md:col-span-2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            <i class="fas fa-upload mr-1"></i> Upload
          </button>
        </div>
      </form>

      <?php if (count($buktis) > 0): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <?php foreach ($buktis as $b): $ext = strtolower(pathinfo($b['nama_file'], PATHINFO_EXTENSION)); ?>
            <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
              <a href="uploads/bukti/<?= htmlspecialchars($b['nama_file']) ?>" target="_blank" class="block">
                <img src="uploads/bukti/<?= htmlspecialchars($b['nama_file']) ?>" class="h-32 w-full object-cover rounded-lg border border-gray-200" alt="bukti">
                <?php if (!empty($b['keterangan'])): ?>
                  <span class="block text-xs text-gray-500 mt-1"><?= htmlspecialchars($b['keterangan']) ?></span>
                <?php endif; ?>
              </a>
            <?php else: ?>
              <a href="uploads/bukti/<?= htmlspecialchars($b['nama_file']) ?>" target="_blank"
                 class="flex items-center justify-center h-32 bg-gray-100 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-200 transition text-center px-2">
                <span><i class="fas fa-file text-2xl mb-1 block"></i><?= strtoupper($ext) ?> - <?= round($b['ukuran'] / 1024) ?> KB</span>
              </a>
              <?php if (!empty($b['keterangan'])): ?>
                <span class="block text-xs text-gray-500 mt-1"><?= htmlspecialchars($b['keterangan']) ?></span>
              <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-400 text-sm">Belum ada barang bukti.</p>
      <?php endif; ?>
    </div>

    <!-- Riwayat Status -->
    <div class="bg-white rounded-xl shadow p-6">
      <h4 class="text-lg font-bold mb-4 text-gray-800">Riwayat Status</h4>
      <?php if (count($statusLogs) > 0): ?>
        <ol class="relative border-l border-gray-200 ml-3">
          <?php foreach ($statusLogs as $s): ?>
            <li class="mb-4 ml-6">
              <span class="absolute -left-[7px] mt-1 w-3.5 h-3.5 rounded-full bg-blue-500"></span>
              <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($s['status_lama']) ?> → <?= htmlspecialchars($s['status_baru']) ?></p>
              <p class="text-xs text-gray-400"><?= htmlspecialchars($s['created_at']) ?> (user id: <?= (int) $s['changed_by'] ?>)</p>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php else: ?>
        <p class="text-gray-400 text-sm">Belum ada riwayat status.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'Footer.php'; ?>
