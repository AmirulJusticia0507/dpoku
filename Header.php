<?php
if (session_status() === PHP_SESSION_NONE) {
//     session_start();
}
?>
<!-- Navbar/Header -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav ms-auto me-3">
    <li class="nav-item d-flex align-items-center">
      <div class="user-panel d-flex align-items-center me-3">
        <div class="info">
          <a href="#" class="d-block fw-bold text-dark">
            <?= isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>
          </a>
        </div>
      </div>
      <?php if (isset($_SESSION['user_id'])): ?>
        <form action="logout.php" method="POST" style="display:inline;">
          <button type="submit" class="btn btn-danger">Logout</button>
        </form>
      <?php endif; ?>
    </li>
  </ul>
</nav>
