<?php
// audit_log.php — Halaman lihat log audit
$page_title = 'Audit Log';
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter opsional (prepared)
$where = [];
$params = [];
if (!empty($_GET['action'])) {
    $where[] = "action = ?";
    $params[] = $_GET['action'];
}
if (!empty($_GET['table_name'])) {
    $where[] = "table_name = ?";
    $params[] = $_GET['table_name'];
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM audit_log $whereSql ORDER BY id DESC LIMIT 100";
$stmt = $koneksidpogendeng->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();

include 'Header.php';
?>

<div class="bg-white rounded-xl shadow p-6">
  <h3 class="text-xl font-bold mb-4">Audit Log</h3>

  <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <input type="text" name="action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>"
           class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Action (create/update/delete)">
    <input type="text" name="table_name" value="<?= htmlspecialchars($_GET['table_name'] ?? '') ?>"
           class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Tabel">
    <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition">Filter</button>
    <a href="audit_log.php" class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition">Reset</a>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-3 py-2">#ID</th>
          <th class="px-3 py-2">Waktu</th>
          <th class="px-3 py-2">User</th>
          <th class="px-3 py-2">Aksi</th>
          <th class="px-3 py-2">Tabel</th>
          <th class="px-3 py-2">Record ID</th>
          <th class="px-3 py-2">Detail</th>
          <th class="px-3 py-2">IP</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if (count($result) > 0): ?>
          <?php foreach ($result as $r): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2"><?= $r['id'] ?></td>
              <td class="px-3 py-2"><?= $r['created_at'] ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($r['username'] ?? $r['user_id']) ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($r['action']) ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($r['table_name']) ?></td>
              <td class="px-3 py-2"><?= $r['record_id'] ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($r['detail'] ?? '-') ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($r['ip_address'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="px-3 py-2 text-center text-gray-400">Belum ada log.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'Footer.php'; ?>
