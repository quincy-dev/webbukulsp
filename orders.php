<?php
// Menampilkan riwayat pesanan milik pengguna yang login.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
$user = $_SESSION['user'] ?? null;
if (!$user) {
    // Mengarahkan pengguna ke halaman tujuan.
header('Location: login.php');
    exit;
}

/* Pastikan tabel transaksi tersedia meskipun user membuka halaman ini sebelum checkout pertama. */
$conn->query("CREATE TABLE IF NOT EXISTS pesanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 nama_penerima VARCHAR(120) NOT NULL,
 telepon VARCHAR(30) NOT NULL,
 alamat TEXT NOT NULL,
 catatan TEXT NULL,
 total DECIMAL(12,2) NOT NULL DEFAULT 0,
 status VARCHAR(30) NOT NULL DEFAULT 'Menunggu',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS detail_pesanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 pesanan_id INT NOT NULL,
 buku_id INT NOT NULL,
 judul_buku VARCHAR(255) NOT NULL,
 harga DECIMAL(12,2) NOT NULL,
 jumlah INT NOT NULL,
 subtotal DECIMAL(12,2) NOT NULL,
 INDEX(pesanan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$userId = (int)$user['id'];
$stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT * FROM pesanan WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$details = [];
if ($orders) {
    $detailStmt = $conn->prepare("SELECT judul_buku, harga, jumlah, subtotal FROM detail_pesanan WHERE pesanan_id = ? ORDER BY id ASC");
    foreach ($orders as $order) {
        $orderId = (int)$order['id'];
        $detailStmt->bind_param('i', $orderId);
        $detailStmt->execute();
        $details[$orderId] = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
function statusBadgeClass($status) {
    switch ($status) {
        case 'Diproses': return 'text-bg-primary';
        case 'Dikirim': return 'text-bg-info';
        case 'Selesai': return 'text-bg-success';
        case 'Dibatalkan': return 'text-bg-danger';
        default: return 'text-bg-warning';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Status Pesanan Saya - BookStore</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
<main class="container py-5">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Pesanan Saya</h2>
        <p class="text-muted mb-0">Pantau status dan rincian pesanan yang sudah kamu buat.</p>
    </div>
    <?php if (!$orders): ?>
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
            <i class="bi bi-receipt display-4 text-muted"></i>
            <h5 class="mt-3">Belum ada pesanan</h5>
            <p class="text-muted">Pesanan yang kamu buat akan muncul di halaman ini.</p>
            <a href="books.php" class="btn btn-primary">Jelajahi Koleksi</a>
        </div></div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($orders as $order): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="fw-bold">Pesanan #<?= (int)$order['id'] ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($order['created_at']) ?></div>
                            </div>
                            <span class="badge <?= statusBadgeClass($order['status']) ?> px-3 py-2"><?= htmlspecialchars($order['status']) ?></span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="small text-muted mb-1">Daftar buku</div>
                                <?php foreach (($details[$order['id']] ?? []) as $item): ?>
                                    <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                                        <div><?= htmlspecialchars($item['judul_buku']) ?><span class="text-muted small"> × <?= (int)$item['jumlah'] ?></span></div>
                                        <strong class="text-nowrap">Rp <?= number_format((float)$item['subtotal'], 0, ',', '.') ?></strong>
                                    </div>
                                <?php endforeach; ?>
                                <div class="d-flex justify-content-between pt-3">
                                    <strong>Total</strong><strong class="text-danger">Rp <?= number_format((float)$order['total'], 0, ',', '.') ?></strong>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="small text-muted mb-1">Dikirim kepada</div>
                                <div class="fw-semibold"><?= htmlspecialchars($order['nama_penerima']) ?></div>
                                <div><?= htmlspecialchars($order['telepon']) ?></div>
                                <div class="text-muted small"><?= nl2br(htmlspecialchars($order['alamat'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
