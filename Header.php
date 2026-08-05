<?php
// Header.php - layout atas: head (Tailwind) + sidebar + topbar
include 'session.php';
$page_title = $page_title ?? 'DPOKU';
$current    = basename($_SERVER['PHP_SELF']);

$navItems = [
    ['href' => 'index.php',              'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
    ['href' => 'Inputdpo.php',           'icon' => 'fas fa-user-secret',    'label' => 'Input DPO'],
    ['href' => 'Inputinstansi.php',      'icon' => 'fas fa-building',       'label' => 'Input Instansi'],
    ['href' => 'Inputjeniskasus.php',    'icon' => 'fas fa-gavel',          'label' => 'Input Jenis Kasus'],
    ['href' => 'Inputjenishukuman.php',  'icon' => 'fas fa-balance-scale',  'label' => 'Input Jenis Hukuman'],
    ['href' => 'Usermanagement.php',     'icon' => 'fas fa-users-cog',      'label' => 'User Management'],
    ['href' => 'Bounty.php',             'icon' => 'fas fa-money-bill-wave','label' => 'Bounty'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
  .nav-link-dpoku {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .55rem .75rem;
    border-radius: .5rem;
    font-size: .875rem;
    font-weight: 500;
    color: #cbd5e1;
    transition: all .15s ease;
  }
  .nav-link-dpoku:hover { background: #1f2937; color: #fff; }
  .nav-link-dpoku.active { background: #1f2937; color: #fff; }
  .rupiah-input { text-align: right; }
  /* DataTables: sesuaikan agar nyambung dengan tema Tailwind */
  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select {
    padding: .35rem .6rem;
    border: 1px solid #d1d5db;
    border-radius: .5rem;
    outline: none;
  }
  .dataTables_wrapper .dataTables_filter input:focus,
  .dataTables_wrapper .dataTables_length select:focus { border-color: #3b82f6; }
</style>
</head>
<body class="bg-gray-100">
<div class="flex min-h-screen">

  <!-- ================= SIDEBAR ================= -->
  <aside id="sidebar" class="w-64 bg-gray-900 flex flex-col shrink-0">
    <div class="p-4 border-b border-gray-800">
      <a href="index.php" class="flex flex-col items-center gap-2">
        <img src="dpoku.png" alt="DPOKU" class="w-20 h-20 object-cover rounded-full">
        <span class="text-white font-bold text-lg tracking-wide">DPOKU</span>
      </a>
    </div>
    <nav id="menu" class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
      <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-link-dpoku <?= $current === $item['href'] ? 'active' : '' ?>">
          <i class="<?= $item['icon'] ?> w-5 text-center"></i>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- ================= CONTENT ================= -->
  <div class="flex-1 flex flex-col min-w-0">

    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 shadow-sm px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <button id="sidebarToggle" type="button" class="text-gray-500 hover:text-gray-800 text-xl leading-none">
          <i class="fas fa-bars"></i>
        </button>
        <a href="export_dpo.php" class="text-green-600 hover:text-green-700 font-semibold text-sm whitespace-nowrap">
          <i class="fas fa-file-csv mr-1"></i> Export CSV DPO
        </a>
      </div>
      <div class="flex items-center gap-3">
        <span class="font-semibold text-gray-700 text-sm hidden sm:inline">
          <?= isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : '' ?>
        </span>
        <form action="logout.php" method="POST" class="inline">
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-1"></i> Logout
          </button>
        </form>
      </div>
    </header>

    <main class="flex-1 p-4 sm:p-6">
