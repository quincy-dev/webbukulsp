<?php
// Navigasi utama yang dipakai di halaman user.

$currentUser = $_SESSION['user'] ?? null;
$isAdmin = $currentUser && (($currentUser['role'] ?? '') === 'admin');
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-book-half me-2"></i>BookStore</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Buka navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <div class="ms-auto d-flex flex-column flex-lg-row align-items-lg-center gap-3 py-2 py-lg-0">
        <a class="nav-link" href="index.php">Beranda</a>
        <a class="nav-link" href="books.php">Koleksi</a><a class="nav-link" href="about.php">About Us</a><a class="nav-link" href="contact.php">Kontak</a>
        <a class="nav-link" href="cart.php">Keranjang</a>
        <a class="nav-link" href="orders.php">Pesanan Saya</a>
        <?php if ($currentUser): ?>
          <?php if ($isAdmin): ?><a class="nav-link" href="admin/index.php">Admin</a><?php endif; ?>
          <span class="small">Halo, <?= htmlspecialchars($currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'Pengguna') ?></span>
          <a class="btn btn-outline-secondary btn-sm" href="logout.php">Keluar</a>
        <?php else: ?>
          <a class="btn btn-outline-primary btn-sm" href="login.php">Masuk</a>
          <a class="btn btn-primary btn-sm" href="register.php">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
