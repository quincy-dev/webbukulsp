<?php
// Daftar buku di halaman pengelolaan admin.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once '../../config/database.php';
if (empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = 0;

if ($search !== '') {
    $countQuery = "SELECT COUNT(*) AS total FROM buku WHERE judul LIKE ? OR penulis LIKE ?";
    $countStmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare($countQuery);
    $pattern = "%{$search}%";
    $countStmt->bind_param('ss', $pattern, $pattern);
    $countStmt->execute();
    $totalBooks = (int) $countStmt->get_result()->fetch_assoc()['total'];

    $query = "SELECT b.*, k.nama_kategori
        FROM buku b
        LEFT JOIN kategori k ON b.id_kategori = k.id
        WHERE b.judul LIKE ? OR b.penulis LIKE ?
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $pattern, $pattern, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $totalBooks = (int) $conn->query("SELECT COUNT(*) AS total FROM buku")->fetch_assoc()['total'];

    $query = "SELECT b.*, k.nama_kategori
        FROM buku b
        LEFT JOIN kategori k ON b.id_kategori = k.id
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

$totalPages = $totalBooks > 0 ? (int) ceil($totalBooks / $perPage) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <title>Kelola Buku - Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <nav class="navbar navbar-expand bg-body-tertiary shadow-sm"><div class="container"><a class="navbar-brand fw-bold" href="../../index.php">BookStore Admin</a><div class="ms-auto d-flex gap-3"><a class="nav-link" href="../index.php">Dashboard</a><a class="nav-link" href="../kategori.php">Kategori</a><a class="nav-link" href="../pesanan.php">Pesanan</a><a class="nav-link" href="../../index.php">Lihat Toko</a></div></div></nav>
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3"><h1>Kelola Buku</h1><a href="../index.php" class="btn btn-outline-secondary">Dashboard</a></div>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari buku..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2 mb-3"><a href="create.php" class="btn btn-success">Tambah Buku</a><a href="../kategori.php" class="btn btn-outline-primary">Kelola Kategori</a></div>

            <div class="table-responsive"><table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= htmlspecialchars($row['penulis']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                            <td>Rp <?= number_format((float) $row['harga'], 0, ',', '.') ?></td>
                            <td><?= $row['stok'] ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?page=<?= max(1, $page - 1) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=<?= $i ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?page=<?= min($totalPages, $page + 1) ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </body>
</html>
