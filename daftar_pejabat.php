<?php
// daftar_pejabat.php - browse & cari daftar pejabat (BPN / KABINET / DPR)
// Mode AJAX (DataTables server-side) saat request POST dengan param "draw".
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['draw'])) {
    header('Content-Type: application/json');
    $draw    = (int) $_POST['draw'];
    $start   = (int) ($_POST['start'] ?? 0);
    $length  = (int) ($_POST['length'] ?? 10);
    $search  = trim($_POST['search']['value'] ?? '');
    $orderCol = (int) ($_POST['order'][0]['column'] ?? 0);
    $orderDir = ($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

    $cols    = ['nama', 'jabatan', 'instansi', 'sumber', 'keterangan'];
    $orderBy = $cols[$orderCol] ?? 'nama';
    $orderBy = in_array($orderBy, ['nama', 'jabatan', 'instansi', 'sumber', 'keterangan'], true)
        ? $orderBy : 'nama';

    $where  = [];
    $params = [];
    if ($search !== '') {
        $like   = "%$search%";
        $where[]  = '(nama ILIKE ? OR jabatan ILIKE ? OR instansi ILIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    foreach (['sumber' => 20, 'instansi' => 255] as $f => $max) {
        $v = trim($_GET[$f] ?? '');
        if ($v !== '') {
            $v = substr($v, 0, $max);
            $where[]  = "daftar_pejabat.$f = ?";
            $params[] = $v;
        }
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $count = function () use ($koneksidpogendeng, $whereSql, $params) {
        $s = $koneksidpogendeng->prepare("SELECT COUNT(*) FROM daftar_pejabat $whereSql");
        $s->execute($params);
        return (int) $s->fetchColumn();
    };
    $total    = (int) $koneksidpogendeng->query('SELECT COUNT(*) FROM daftar_pejabat')->fetchColumn();
    $filtered = $count();

    $sql = "SELECT id, nama, jabatan, instansi, keterangan, sumber
            FROM daftar_pejabat $whereSql
            ORDER BY $orderBy $orderDir, id
            LIMIT $length OFFSET $start";
    $s = $koneksidpogendeng->prepare($sql);
    $s->execute($params);

    $data = [];
    foreach ($s->fetchAll() as $r) {
        $data[] = [
            htmlspecialchars($r['nama']),
            htmlspecialchars($r['jabatan']),
            htmlspecialchars($r['instansi']),
            htmlspecialchars($r['sumber']),
            htmlspecialchars($r['keterangan']),
            '<a href="detail_pejabat.php?id=' . (int) $r['id'] . '" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg transition"><i class="fas fa-eye"></i></a>',
        ];
    }

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $total,
        'recordsFiltered' => $filtered,
        'data'            => $data,
    ]);
    exit;
}

$page_title = 'Daftar Pejabat';
include 'Header.php';

$tot = (int) $koneksidpogendeng->query('SELECT COUNT(*) FROM daftar_pejabat')->fetchColumn();
$perSumber = $koneksidpogendeng->query(
    "SELECT sumber, COUNT(*) AS jumlah FROM daftar_pejabat GROUP BY sumber ORDER BY jumlah DESC"
)->fetchAll();
$instansiList = $koneksidpogendeng->query(
    "SELECT DISTINCT instansi FROM daftar_pejabat WHERE instansi <> '' ORDER BY instansi"
)->fetchAll();

$fSumber   = trim($_GET['sumber'] ?? '');
$fInstansi = trim($_GET['instansi'] ?? '');
$badge = ['BPN' => 'bg-blue-600', 'KABINET' => 'bg-green-600', 'DPR' => 'bg-purple-600'];
?>

<!-- ===================== STAT ===================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl"><i class="fas fa-id-badge"></i></div>
    <div>
      <p class="text-2xl font-bold text-gray-800"><?= number_format($tot, 0, ',', '.') ?></p>
      <p class="text-sm text-gray-500">Total Pejabat</p>
    </div>
  </div>
  <?php foreach ($perSumber as $ps): ?>
    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
      <div class="w-12 h-12 rounded-full <?= $badge[$ps['sumber']] ?? 'bg-gray-100' ?> bg-opacity-20 flex items-center justify-center text-xl">
        <i class="fas <?= $ps['sumber'] === 'BPN' ? 'fa-building' : ($ps['sumber'] === 'KABINET' ? 'fa-landmark' : 'fa-users') ?> text-gray-700"></i>
      </div>
      <div>
        <p class="text-2xl font-bold text-gray-800"><?= number_format((int) $ps['jumlah'], 0, ',', '.') ?></p>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($ps['sumber']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ===================== FILTER ===================== -->
<div class="bg-white rounded-xl shadow p-5 mb-6">
  <form method="GET" action="daftar_pejabat.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Cari (Nama / Jabatan / Instansi)</label>
      <input type="text" name="q" value="<?= htmlspecialchars(trim($_GET['q'] ?? '')) ?>"
             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="mis. Prabowo">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Sumber</label>
      <select name="sumber" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">Semua Sumber</option>
        <option value="BPN" <?= $fSumber === 'BPN' ? 'selected' : '' ?>>BPN</option>
        <option value="KABINET" <?= $fSumber === 'KABINET' ? 'selected' : '' ?>>KABINET</option>
        <option value="DPR" <?= $fSumber === 'DPR' ? 'selected' : '' ?>>DPR</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Instansi</label>
      <select name="instansi" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">Semua Instansi</option>
        <?php foreach ($instansiList as $i): ?>
          <option value="<?= htmlspecialchars($i['instansi']) ?>" <?= $fInstansi === $i['instansi'] ? 'selected' : '' ?>><?= htmlspecialchars($i['instansi']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition flex-1"><i class="fas fa-filter mr-1"></i> Filter</button>
      <a href="daftar_pejabat.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition"><i class="fas fa-times"></i></a>
    </div>
  </form>
</div>

<!-- ===================== TABEL ===================== -->
<div class="bg-white rounded-xl shadow p-6">
  <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
    <h3 class="text-xl font-bold text-gray-800">Daftar Pejabat</h3>
    <span class="text-sm text-gray-500"><span id="jmlTerfilter" class="font-bold">0</span> hasil</span>
  </div>
  <div class="overflow-x-auto">
    <table id="pejabatTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">Nama</th>
          <th class="px-3 py-2">Jabatan</th>
          <th class="px-3 py-2">Instansi</th>
          <th class="px-3 py-2">Sumber</th>
          <th class="px-3 py-2">Keterangan</th>
          <th class="px-3 py-2">Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<script>
  $(document).ready(function () {
    var url = 'daftar_pejabat.php?q=' + encodeURIComponent(<?= json_encode(trim($_GET['q'] ?? '')) ?>)
      + '&sumber=' + encodeURIComponent(<?= json_encode($fSumber) ?>)
      + '&instansi=' + encodeURIComponent(<?= json_encode($fInstansi) ?>);

    $('#pejabatTable').DataTable({
      serverSide: true,
      ajax: url,
      order: [[0, 'asc']],
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
      drawCallback: function (settings) {
        $('#jmlTerfilter').text(settings.fnRecordsDisplay());
      }
    });
  });
</script>

<?php include 'Footer.php'; ?>
