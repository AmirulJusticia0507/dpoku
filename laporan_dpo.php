<?php
// laporan_dpo.php - pencarian & laporan DPO (server-side DataTables) + export CSV/Excel/PDF
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['draw'])) {
    header('Content-Type: application/json');
    $draw     = (int) $_POST['draw'];
    $start    = (int) ($_POST['start'] ?? 0);
    $length   = (int) ($_POST['length'] ?? 10);
    $search   = trim($_POST['search']['value'] ?? '');
    $orderCol = (int) ($_POST['order'][0]['column'] ?? 0);
    $orderDir = ($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

    $cols    = ['dpo.nama_lengkap', 'dpo.nik', 'jenis_kasus.jenis_kasus', 'jenis_hukuman.jenis_hukuman', 'instansi.nama_instansi', 'bounty.jumlah_bounty', 'dpo.status_dpo'];
    $orderBy = $cols[$orderCol] ?? 'dpo.nama_lengkap';

    $where  = [];
    $params = [];
    if ($search !== '') {
        $like   = "%$search%";
        $where[]  = '(dpo.nama_lengkap ILIKE ? OR dpo.nik ILIKE ? OR jenis_kasus.jenis_kasus ILIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    foreach (['status_dpo' => 50, 'jenis_kasus' => 255, 'nama_instansi' => 255] as $f => $max) {
        $v = trim($_GET[$f] ?? '');
        if ($v !== '') {
            $v = substr($v, 0, $max);
            $col = $f === 'jenis_kasus' ? 'jenis_kasus.jenis_kasus' : ($f === 'nama_instansi' ? 'instansi.nama_instansi' : 'dpo.status_dpo');
            $where[]  = "$col = ?";
            $params[] = $v;
        }
    }

    $from = "FROM dpo
             LEFT JOIN instansi ON instansi.id = dpo.instansi_id
             LEFT JOIN jenis_kasus ON jenis_kasus.id = dpo.jenis_kasus_id
             LEFT JOIN jenis_hukuman ON jenis_hukuman.id = dpo.jenis_hukuman_id
             LEFT JOIN bounty ON bounty.id_kasus = dpo.jenis_kasus_id";
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $count = function () use ($koneksidpogendeng, $from, $whereSql, $params) {
        $s = $koneksidpogendeng->prepare("SELECT COUNT(*) $from $whereSql");
        $s->execute($params);
        return (int) $s->fetchColumn();
    };
    $total    = (int) $koneksidpogendeng->query('SELECT COUNT(*) FROM dpo')->fetchColumn();
    $filtered = $count();

    $sql = "SELECT dpo.id, dpo.nama_lengkap, dpo.nik, dpo.status_dpo, dpo.foto,
                   jenis_kasus.jenis_kasus AS nama_kasus,
                   jenis_hukuman.jenis_hukuman AS nama_hukuman,
                   instansi.nama_instansi,
                   COALESCE(bounty.jumlah_bounty, 0) AS jumlah_bounty
            $from $whereSql
            ORDER BY $orderBy $orderDir, dpo.id
            LIMIT $length OFFSET $start";
    $s = $koneksidpogendeng->prepare($sql);
    $s->execute($params);

    $badge = ['BURON' => 'bg-red-600', 'TERTANGKAP' => 'bg-green-600', 'MENINGGAL DUNIA' => 'bg-gray-500'];
    $data = [];
    foreach ($s->fetchAll() as $r) {
        $badgeCls = $badge[$r['status_dpo']] ?? 'bg-gray-500';
        $data[] = [
            htmlspecialchars($r['nama_lengkap']),
            htmlspecialchars($r['nik']),
            htmlspecialchars($r['nama_kasus'] ?? '-'),
            htmlspecialchars($r['nama_hukuman'] ?? '-'),
            htmlspecialchars($r['nama_instansi'] ?? '-'),
            'Rp ' . number_format((float) $r['jumlah_bounty'], 0, ',', '.'),
            '<span class="inline-block ' . $badgeCls . ' text-white text-xs font-bold px-3 py-1 rounded-full">' . htmlspecialchars($r['status_dpo']) . '</span>',
            '<a href="detail_dpo.php?id=' . (int) $r['id'] . '" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-lg transition"><i class="fas fa-eye"></i></a>',
        ];
    }

    echo json_encode([
        'draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data,
    ]);
    exit;
}

$page_title = 'Laporan DPO';
include 'Header.php';

$kasusList   = $koneksidpogendeng->query('SELECT id, jenis_kasus FROM jenis_kasus ORDER BY jenis_kasus')->fetchAll();
$instansiList = $koneksidpogendeng->query('SELECT id, nama_instansi FROM instansi ORDER BY nama_instansi')->fetchAll();

$fStatus   = trim($_GET['status_dpo'] ?? '');
$fKasus    = trim($_GET['jenis_kasus'] ?? '');
$fInstansi = trim($_GET['nama_instansi'] ?? '');
$fQ        = trim($_GET['q'] ?? '');

$exportQS = http_build_query(array_filter([
    'status_dpo'    => $fStatus,
    'jenis_kasus'   => $fKasus,
    'nama_instansi' => $fInstansi,
    'nama_lengkap'  => $fQ,
]));
?>

<div class="bg-white rounded-xl shadow p-5 mb-6">
  <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
    <h3 class="text-xl font-bold text-gray-800">Laporan DPO</h3>
    <div class="flex gap-2">
      <a href="export_dpo.php?<?= htmlspecialchars($exportQS) ?>" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition"><i class="fas fa-file-csv mr-1"></i> CSV</a>
      <a href="export_dpo_excel.php?<?= htmlspecialchars($exportQS) ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition"><i class="fas fa-file-excel mr-1"></i> Excel</a>
      <a href="export_dpo_pdf.php?<?= htmlspecialchars($exportQS) ?>" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition"><i class="fas fa-file-pdf mr-1"></i> PDF</a>
    </div>
  </div>

  <form method="GET" action="laporan_dpo.php" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Cari (Nama / NIK / Kasus)</label>
      <input type="text" name="q" value="<?= htmlspecialchars($fQ) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="mis. Bagus">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
      <select name="status_dpo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">Semua Status</option>
        <option value="BURON" <?= $fStatus === 'BURON' ? 'selected' : '' ?>>BURON</option>
        <option value="TERTANGKAP" <?= $fStatus === 'TERTANGKAP' ? 'selected' : '' ?>>TERTANGKAP</option>
        <option value="MENINGGAL DUNIA" <?= $fStatus === 'MENINGGAL DUNIA' ? 'selected' : '' ?>>MENINGGAL DUNIA</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kasus</label>
      <select name="jenis_kasus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">Semua Kasus</option>
        <?php foreach ($kasusList as $k): ?>
          <option value="<?= htmlspecialchars($k['jenis_kasus']) ?>" <?= $fKasus === $k['jenis_kasus'] ? 'selected' : '' ?>><?= htmlspecialchars($k['jenis_kasus']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Instansi</label>
      <select name="nama_instansi" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">Semua Instansi</option>
        <?php foreach ($instansiList as $i): ?>
          <option value="<?= htmlspecialchars($i['nama_instansi']) ?>" <?= $fInstansi === $i['nama_instansi'] ? 'selected' : '' ?>><?= htmlspecialchars($i['nama_instansi']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition flex-1"><i class="fas fa-filter mr-1"></i> Filter</button>
      <a href="laporan_dpo.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition"><i class="fas fa-times"></i></a>
    </div>
  </form>
</div>

<div class="bg-white rounded-xl shadow p-6">
  <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
    <h3 class="text-xl font-bold text-gray-800">Data DPO</h3>
    <span class="text-sm text-gray-500"><span id="jmlTerfilter" class="font-bold">0</span> hasil</span>
  </div>
  <div class="overflow-x-auto">
    <table id="laporanTable" class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
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
    </table>
  </div>
</div>

<script>
  $(document).ready(function () {
    var url = 'laporan_dpo.php?q=' + encodeURIComponent(<?= json_encode($fQ) ?>)
      + '&status_dpo=' + encodeURIComponent(<?= json_encode($fStatus) ?>)
      + '&jenis_kasus=' + encodeURIComponent(<?= json_encode($fKasus) ?>)
      + '&nama_instansi=' + encodeURIComponent(<?= json_encode($fInstansi) ?>);

    $('#laporanTable').DataTable({
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
