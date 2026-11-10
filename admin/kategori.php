<?php
// Admin mengelola data kategori buku.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}
$message = '';
$messageType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama === '') {
            $message = 'Nama kategori wajib diisi.';
            $messageType = 'danger';
        } else {
            $check = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT id FROM kategori WHERE nama_kategori = ? LIMIT 1");
            $check->bind_param('s', $nama);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message = 'Kategori tersebut sudah tersedia.';
                $messageType = 'warning';
            } else {
                $insert = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                $insert->bind_param('s', $nama);
                if ($insert->execute()) $message = 'Kategori berhasil ditambahkan.';
                else { $message = 'Kategori gagal ditambahkan.'; $messageType = 'danger'; }
            }
        }
    } elseif (($_POST['action'] ?? '') === 'update') {
        $id=(int)($_POST['id']??0);$nama=trim($_POST['nama_kategori']??'');
        if($id>0 && $nama!==''){$up=$conn->prepare("UPDATE kategori SET nama_kategori=? WHERE id=?");$up->bind_param('si',$nama,$id);$up->execute();$message='Kategori diperbarui.';}
        else{$message='Nama kategori wajib diisi.';$messageType='danger';}
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $used = $conn->prepare("SELECT COUNT(*) AS total FROM buku WHERE id_kategori = ?");
        $used->bind_param('i', $id);
        $used->execute();
        $totalUsed = (int) ($used->get_result()->fetch_assoc()['total'] ?? 0);
        if ($totalUsed > 0) {
            $message = "Kategori tidak bisa dihapus karena masih digunakan oleh {$totalUsed} buku. Ubah kategori buku tersebut terlebih dahulu.";
            $messageType = 'warning';
        } else {
            $delete = $conn->prepare("DELETE FROM kategori WHERE id = ?");
            $delete->bind_param('i', $id);
            if ($delete->execute() && $delete->affected_rows > 0) $message = 'Kategori berhasil dihapus.';
            else { $message = 'Kategori tidak ditemukan atau gagal dihapus.'; $messageType = 'danger'; }
        }
    }
}
$kategori = $conn->query("SELECT k.id, k.nama_kategori, COUNT(b.id) AS jumlah_buku FROM kategori k LEFT JOIN buku b ON b.id_kategori = k.id GROUP BY k.id, k.nama_kategori ORDER BY k.nama_kategori")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kelola Kategori - Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<nav class="navbar bg-white shadow-sm"><div class="container"><a class="navbar-brand fw-bold" href="../index.php">BookStore Admin</a><div class="d-flex gap-3"><a class="nav-link" href="index.php">Dashboard</a><a class="nav-link" href="buku/index.php">Kelola Buku</a><a class="nav-link" href="../index.php">Lihat Toko</a></div></div></nav>
<main class="container py-5" style="max-width:900px"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><div><h1 class="h3 mb-1">Kelola Kategori</h1><p class="text-muted mb-0">Tambah dan hapus kategori buku.</p></div><a href="index.php" class="btn btn-outline-secondary">Kembali ke Dashboard</a></div>
<?php if ($message): ?><div class="alert alert-<?= htmlspecialchars($messageType) ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="card shadow-sm mb-4"><div class="card-body"><h2 class="h5 mb-3">Tambah Kategori</h2><form method="post" class="row g-2"><input type="hidden" name="action" value="add"><div class="col-md-9"><input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Novel, Teknologi, Pendidikan" maxlength="100" required></div><div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Tambah Kategori</button></div></form></div></div>
<div class="card shadow-sm"><div class="card-body"><h2 class="h5 mb-3">Daftar Kategori</h2><div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead><tr><th style="width:80px">ID</th><th>Nama Kategori</th><th>Jumlah Buku</th><th style="width:130px">Aksi</th></tr></thead><tbody>
<?php if (!$kategori): ?><tr><td colspan="4" class="text-center text-muted">Belum ada kategori.</td></tr><?php else: foreach ($kategori as $item): ?><tr><td><?= (int)$item['id'] ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><input class="form-control form-control-sm" name="nama_kategori" value="<?= htmlspecialchars($item['nama_kategori']) ?>" required><button class="btn btn-primary btn-sm">Update</button></form></td><td><?= (int)$item['jumlah_buku'] ?></td><td><form method="post" onsubmit="return confirm('Hapus kategori ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="btn btn-outline-danger btn-sm" type="submit" <?= (int)$item['jumlah_buku'] > 0 ? 'disabled title="Kategori masih digunakan oleh buku"' : '' ?>>Hapus</button></form></td></tr><?php endforeach; endif; ?>
</tbody></table></div><p class="form-text mt-3 mb-0">Kategori yang masih digunakan oleh buku tidak dapat dihapus.</p></div></div></main></body></html>
