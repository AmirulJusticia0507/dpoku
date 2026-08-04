<?php
// audit_log.php — Halaman lihat log audit
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter opsional (prepared)
$where = '';
$params = [];
$types = '';
if (!empty($_GET['action'])) {
    $where .= " AND action = ?";
    $params[] = $_GET['action'];
    $types .= 's';
}
if (!empty($_GET['table_name'])) {
    $where .= " AND table_name = ?";
    $params[] = $_GET['table_name'];
    $types .= 's';
}

$sql = "SELECT * FROM audit_log WHERE 1=1 $where ORDER BY id DESC LIMIT 100";
$stmt = $koneksidpogendeng->prepare($sql);
if ($params) {
    $bind = [];
    $bind[] = $types;
    foreach ($params as $k => $v) {
        $bind[] = &$params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
}
$stmt->execute();
$result = $stmt->get_result();

include 'Header.php';
include 'Sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h3 class="mb-2">Audit Log</h3>
    </div>
  </div>
  <section class="content">
    <div class="container-fluid">
      <form method="GET" class="mb-3 row g-3 align-items-center">
        <div class="col-auto">
          <input type="text" name="action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>"
                 class="form-control form-control-sm" placeholder="Action (create/update/delete)">
        </div>
        <div class="col-auto">
          <input type="text" name="table_name" value="<?= htmlspecialchars($_GET['table_name'] ?? '') ?>"
                 class="form-control form-control-sm" placeholder="Tabel">
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-primary">Filter</button>
          <a href="audit_log.php" class="btn btn-sm btn-secondary">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered table-hover datatable">
          <thead class="table-light">
            <tr>
              <th>#ID</th><th>Waktu</th><th>User</th><th>Aksi</th><th>Tabel</th><th>Record ID</th><th>Detail</th><th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $r['id'] ?></td>
                  <td><?= $r['created_at'] ?></td>
                  <td><?= htmlspecialchars($r['username'] ?? $r['user_id']) ?></td>
                  <td><?= htmlspecialchars($r['action']) ?></td>
                  <td><?= htmlspecialchars($r['table_name']) ?></td>
                  <td><?= $r['record_id'] ?></td>
                  <td><?= htmlspecialchars($r['detail'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($r['ip_address'] ?? '-') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted">Belum ada log.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php include 'Footer.php'; ?>
