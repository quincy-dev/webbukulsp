<?php
// Halaman utama: menampilkan buku yang tersedia di toko.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/image.php';

$currentUser = $_SESSION['user'] ?? null;
$perPage = 6;
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$pattern = '%' . $search . '%';
if ($search !== '') {
    $countStmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT COUNT(*) AS total FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.judul LIKE ? OR b.penulis LIKE ? OR k.nama_kategori LIKE ?");
    $countStmt->bind_param('sss', $pattern, $pattern, $pattern);
    $countStmt->execute();
    $totalBooks = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
} else {
    $totalBooks = (int) ($conn->query("SELECT COUNT(*) AS total FROM buku")->fetch_assoc()['total'] ?? 0);
}
$totalPages = max(1, (int) ceil($totalBooks / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
if ($search !== '') {
    $stmt = $conn->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.judul LIKE ? OR b.penulis LIKE ? OR k.nama_kategori LIKE ? ORDER BY b.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('sssii', $pattern, $pattern, $pattern, $perPage, $offset);
} else {
    $stmt = $conn->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id ORDER BY b.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStore - Toko Buku Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-book-half me-2"></i>BookStore</a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a class="nav-link text-white" href="index.php">Beranda</a>
                <a class="nav-link text-white" href="books.php">Koleksi</a>
                <a class="nav-link text-white" href="cart.php">Keranjang</a>
                <?php if ($currentUser): ?>
                <span class="text-white small">Halo, <?= htmlspecialchars($currentUser['nama_lengkap']) ?></span>
                <a class="btn btn-light btn-sm" href="logout.php">Keluar</a>
                <?php else: ?>
                <a class="btn btn-light btn-sm" href="login.php">Masuk</a>
                <a class="btn btn-outline-light btn-sm" href="register.php">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav> -->

    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://i.pinimg.com/1200x/2c/8d/db/2c8ddbbc9d3d540dc19fb12e8f6c83ee.jpg"
                    class="d-block w-100 hero-image" alt="Banner" style="filter: blur(5px);">
                <div class="carousel-caption d-none d-md-block">
                    <h2>Selamat Datang di BookStore</h2>
                    <a href="books.php" class="btn btn-primary btn-lg">Lihat Koleksi</a>
                </div>
            </div>
        </div>
    </div> -->

    <div class="container ">
        <div class="text-center">
            <h2>Selamat Datang di BookStore</h2>
            <a href="books.php" class="btn btn-primary btn-lg">Lihat Koleksi</a>
        </div>
    </div>
    <br>
    <div class="container">
        <div class="search">
            <form class="d-flex" role="search" method="GET" action="index.php">
                <input class="form-control me-2" type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul, penulis, atau kategori..." aria-label="Cari buku" />
                <button class="btn btn-outline-success" type="submit">Cari</button>
                <?php if ($search !== ''): ?><a class="btn btn-outline-secondary ms-2" href="index.php">Reset</a><?php endif; ?>
            </form>
        </div>
    </div>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold"><?= $search !== '' ? 'Hasil pencarian' : 'Buku Terbaru' ?></h2><?php if ($search !== ''): ?><p class="text-muted">Kata kunci: <?= htmlspecialchars($search) ?> (<?= $totalBooks ?> hasil)</p><?php endif; ?>
            </div>

            <?php if (empty($books)): ?>
                <div class="alert alert-info text-center"><?= $search !== '' ? 'Buku tidak ditemukan. Coba kata kunci lain.' : 'Belum ada buku yang tersedia.' ?></div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($books as $book): ?>
                        <?php
                        $image = bookstoreImageSrc($book['gambar'] ?? '');
                        $hargaLama = !empty($book['harga_lama']) ? (float) $book['harga_lama'] : (float) $book['harga'];
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm book-card">
                                <div class="position-relative">
                                    <img src="<?= htmlspecialchars($image) ?>" class="card-img-top"
                                        alt="<?= htmlspecialchars($book['judul']) ?>" style="height: 250px; object-fit: cover;">
                                    <?php if (!empty($book['diskon']) && (int) $book['diskon'] > 0): ?>
                                        <span
                                            class="badge bg-danger position-absolute top-0 start-0 m-2">-<?= (int) $book['diskon'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($book['judul']) ?></h5>
                                    <p class="card-text text-muted small mb-2"><?= htmlspecialchars($book['penulis']) ?></p>
                                    <p class="text-muted small mb-3">
                                        <?= htmlspecialchars($book['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                    </p>
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-decoration-line-through text-muted small">Rp
                                                <?= number_format($hargaLama, 0, ',', '.') ?></span>
                                            <?php if ((float) $book['harga'] < $hargaLama): ?>
                                                <span class="badge bg-success">Promo</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold text-danger fs-5">Rp
                                            <?= number_format((float) $book['harga'], 0, ',', '.') ?>
                                        </div>
                                        <a href="add_to_cart.php?id=<?= $book['id'] ?>"
                                            class="btn btn-primary w-100 mt-3">Tambah ke Keranjang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-white py-5">
        <div class="container">
            <p class="text-center mb-0">&copy; 2024 BookStore. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
        </script>
</body>

</html>