<?php
// Form untuk menambahkan data buku baru.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once '../../config/database.php';
require_once __DIR__ . '/image_helper.php';
if (empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}

$kategori_list = $conn->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $harga = (int) ($_POST['harga'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    [$gambar, $error] = processBookImage($_FILES['gambar'] ?? [], $_POST['gambar_url'] ?? '');

    if (!$error && $judul !== '' && $penulis !== '' && $id_kategori > 0 && $harga >= 0 && $stok >= 0) {
        $stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("INSERT INTO buku (judul, penulis, id_kategori, harga, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiiiss', $judul, $penulis, $id_kategori, $harga, $stok, $deskripsi, $gambar);
        if ($stmt->execute()) {
            // Mengarahkan pengguna ke halaman tujuan.
header('Location: index.php');
            exit;
        }
        $error = 'Buku gagal disimpan: ' . $stmt->error;
    } elseif (!$error) {
        $error = 'Periksa kembali data buku. Semua data wajib diisi dengan benar.';
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tambah Buku - Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<main class="container py-5" style="max-width:760px"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Tambah Buku Baru</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if (!$kategori_list): ?><div class="alert alert-warning">Belum ada kategori. <a href="../kategori.php">Tambahkan kategori terlebih dahulu</a>.</div><?php endif; ?>
<form method="POST" enctype="multipart/form-data">
<div class="mb-3"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required></div>
<div class="mb-3"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($_POST['penulis'] ?? '') ?>" required></div>
<div class="mb-3"><label class="form-label">Kategori</label><select name="id_kategori" class="form-select" required><option value="">Pilih Kategori</option><?php foreach ($kategori_list as $k): ?><option value="<?= (int)$k['id'] ?>" <?= (($_POST['id_kategori'] ?? '') == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option><?php endforeach; ?></select><div class="form-text"><a href="../kategori.php">Kelola kategori</a></div></div>
<div class="row"><div class="col-md-6 mb-3"><label class="form-label">Harga (Rp)</label><input type="number" min="0" name="harga" class="form-control" value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stok" class="form-control" value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>" required></div></div>
<div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea></div>
<div class="mb-3"><label class="form-label">Link Gambar (opsional)</label><input type="url" name="gambar_url" class="form-control" placeholder="https://contoh.com/cover-buku.jpg" value="<?= htmlspecialchars($_POST['gambar_url'] ?? '') ?>"><div class="form-text">Masukkan URL gambar langsung (http/https), atau upload file di bawah.</div></div>
<div class="mb-4"><label class="form-label">Upload Gambar (opsional)</label><input type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"><div class="form-text">JPG, PNG, GIF, WEBP. Maksimal 5 MB. Gunakan salah satu metode gambar saja.</div></div>
<button type="submit" class="btn btn-primary" <?= !$kategori_list ? 'disabled' : '' ?>>Simpan Buku</button> <a href="index.php" class="btn btn-outline-secondary">Batal</a></form></div></div></main></body></html>
