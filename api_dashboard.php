<?php
// api_dashboard.php - data JSON untuk grafik dashboard (real-time via polling)
include 'session.php';
include 'Koneksi.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Statistik inti
$counts = [
    'totalDpo'    => (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo")->fetchColumn(),
    'totalBuron'  => (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo WHERE status_dpo='BURON'")->fetchColumn(),
    'totalTertangkap' => (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo WHERE status_dpo='TERTANGKAP'")->fetchColumn(),
    'totalMeninggal'  => (int) $koneksidpogendeng->query("SELECT COUNT(*) FROM dpo WHERE status_dpo='MENINGGAL DUNIA'")->fetchColumn(),
    'totalBounty' => (int) $koneksidpogendeng->query("SELECT COALESCE(SUM(jumlah_bounty),0) FROM bounty")->fetchColumn(),
];

// Distribusi status
$statusRows = $koneksidpogendeng->query(
    "SELECT status_dpo, COUNT(*) AS jumlah FROM dpo GROUP BY status_dpo"
)->fetchAll();
$statusLabels = array_column($statusRows, 'status_dpo');
$statusData   = array_map('intval', array_column($statusRows, 'jumlah'));

// Kasus terbanyak (top 6)
$kasusRows = $koneksidpogendeng->query(
    "SELECT jenis_kasus.jenis_kasus AS label, COUNT(dpo.id) AS jumlah
     FROM jenis_kasus LEFT JOIN dpo ON dpo.jenis_kasus_id = jenis_kasus.id
     GROUP BY jenis_kasus.id ORDER BY jumlah DESC LIMIT 6"
)->fetchAll();
$kasusLabels = array_column($kasusRows, 'label');
$kasusData   = array_map('intval', array_column($kasusRows, 'jumlah'));

// DPO per instansi (top 6 yang punya DPO)
$instansiRows = $koneksidpogendeng->query(
    "SELECT instansi.nama_instansi AS label, COUNT(dpo.id) AS jumlah
     FROM instansi LEFT JOIN dpo ON dpo.instansi_id = instansi.id
     GROUP BY instansi.id HAVING COUNT(dpo.id) > 0
     ORDER BY jumlah DESC LIMIT 6"
)->fetchAll();
$instansiLabels = array_column($instansiRows, 'label');
$instansiData   = array_map('intval', array_column($instansiRows, 'jumlah'));

// Progres penangkapan per bulan (6 bulan terakhir, dari riwayat status)
$bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan[] = date('Y-m', strtotime("-$i months"));
}
$progresData = [];
foreach ($bulan as $bm) {
    $st = $koneksidpogendeng->prepare(
        "SELECT COUNT(*) FROM dpo_status_log
         WHERE status_baru='TERTANGKAP' AND to_char(created_at, 'YYYY-MM') = ?"
    );
    $st->execute([$bm]);
    $progresData[] = (int) $st->fetchColumn();
}
$progresLabels = array_map(function ($bm) {
    $parts = explode('-', $bm);
    return date('M', mktime(0, 0, 0, (int) $parts[1], 1, (int) $parts[0]));
}, $bulan);

echo json_encode([
    'counts' => $counts,
    'status' => ['labels' => $statusLabels, 'data' => $statusData],
    'kasus'  => ['labels' => $kasusLabels, 'data' => $kasusData],
    'instansi' => ['labels' => $instansiLabels, 'data' => $instansiData],
    'progres' => ['labels' => $progresLabels, 'data' => $progresData],
    'lastUpdate' => date('H:i:s'),
]);
