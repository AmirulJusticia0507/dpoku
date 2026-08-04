<?php
include 'session.php'; 
include 'Koneksi.php'; 
include 'Header.php'; 
include 'Sidebar.php'; 
?>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard DPOKU</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .dynamic-card {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      border-radius: 15px;
      padding: 10px;
      margin-bottom: 15px;
      text-align: center;
    }

    .btn-dynamic {
      background-color: #ff6f61;
      color: white;
      border-radius: 10px;
      margin-bottom: 10px;
    }

    .btn-dynamic:hover {
      background-color: #e04e3b;
    }

    .tab-content {
      background-color: #f4f6f9;
      padding: 20px;
      border-radius: 10px;
      min-height: 300px;
    }

    .readonly-input {
      background-color: #e9ecef;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper"> <!-- Mulai Wrapper -->

    <!-- ✅ Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <!-- ✅ Tombol Hamburger -->
          <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars"></i>
          </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="#" class="nav-link">Home</a>
        </li>
      </ul>
    </nav>

    <!-- ✅ Content -->
    <div class="content-wrapper p-4">
      <div class="container mt-4">
        <div class="row">
          <?php
          $sql = "SELECT dpo.*, bounty.jumlah_bounty FROM dpo 
                  LEFT JOIN bounty ON bounty.id_kasus = dpo.id";
          $result = mysqli_query($koneksidpogendeng, $sql);
          while ($row = mysqli_fetch_assoc($result)) {
          ?>
            <div class="col-md-4">
              <div class="card text-center">
                <img src="uploads/<?php echo $row['foto']; ?>" class="card-img-top" style="height: 300px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?php echo strtoupper($row['nama_lengkap']); ?></h5>
                  <p class="card-text">Bounty: Rp <?php echo number_format($row['jumlah_bounty']); ?> Juta</p>
                  <h6 class="text-danger">DEAD OR ALIVE</h6>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

    <?php include 'Footer.php'; ?> <!-- Footer -->

  </div> <!-- Tutup Wrapper -->

  <!-- ✅ JS AdminLTE -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>
