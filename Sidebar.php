<?php include 'session.php'; ?>
<!-- Sidebar -->
<aside class="main-sidebar sidebar-light elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-center">
      <a href="index.php" class="brand-link text-center">
        <img src="dpoku.png" class="img-circle elevation-2" alt="User Image" style="width: 100px; height: 100px; object-fit: cover;">
        <span class="brand-text font-weight-bold">DPOKU</span>
      </a>
    </div>

    <nav>
      <ul class="nav nav-pills nav-sidebar flex-column" id="menu">
        <li class="nav-item"><a href="index.php" class="btn btn-dynamic w-100"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
        <li class="nav-item"><a href="Inputdpo.php" class="btn btn-dynamic w-100"><i class="fas fa-user-secret me-2"></i> Input DPO</a></li>
        <li class="nav-item"><a href="Inputinstansi.php" class="btn btn-dynamic w-100"><i class="fas fa-building me-2"></i> Input Instansi</a></li>
        <li class="nav-item"><a href="Inputjeniskasus.php" class="btn btn-dynamic w-100"><i class="fas fa-gavel me-2"></i> Input Jenis Kasus</a></li>
        <li class="nav-item"><a href="Inputjenishukuman.php" class="btn btn-dynamic w-100"><i class="fas fa-balance-scale me-2"></i> Input Jenis Hukuman</a></li>
        <li class="nav-item"><a href="Usermanagement.php" class="btn btn-dynamic w-100"><i class="fas fa-users-cog me-2"></i> User Management</a></li>
        <li class="nav-item"><a href="Bounty.php" class="btn btn-dynamic w-100"><i class="fas fa-money-bill-wave me-2"></i> Bounty</a></li>
      </ul>
    </nav>
  </div>
</aside>

<!-- Loading Overlay -->
<div id="loading" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.7); z-index:9999; text-align:center;">
  <div class="spinner-border text-primary" style="margin-top: 20%;" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<!-- FontAwesome & jQuery -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Loading Animation Script -->
<script>
  $(document).ready(function(){
    $('#menu a').on('click', function(e){
      e.preventDefault();
      $('#loading').fadeIn();

      var href = $(this).attr('href');
      setTimeout(function(){ // Simulasi loading 1 detik
        window.location.href = href;
      }, 1000);
    });
  });
</script>
