<?php
// Form untuk mengubah data buku yang sudah ada.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once '../../config/database.php';
require_once __DIR__ . '/image_helper.php';
if (empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}
$id = (int) ($_GET['id'] ?? 0);
$stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
if (!$buku) { // Mengarahkan pengguna ke halaman tujuan.
header('Location: index.php'); exit; }
$kategori_list = $conn->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $harga = (int) ($_POST['harga'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    [$gambar, $error] = processBookImage($_FILES['gambar'] ?? [], $_POST['gambar_url'] ?? '', $buku['gambar'] ?? '');
    if (!$error && $judul !== '' && $penulis !== '' && $id_kategori > 0 && $harga >= 0 && $stok >= 0) {
        $update = $conn->prepare("UPDATE buku SET judul=?, penulis=?, id_kategori=?, harga=?, stok=?, deskripsi=?, gambar=? WHERE id=?");
        $update->bind_param('ssiiissi', $judul, $penulis, $id_kategori, $harga, $stok, $deskripsi, $gambar, $id);
        if ($update->execute()) { header('Location: index.php'); exit; }
        $error = 'Data buku gagal diperbarui: ' . $update->error;
    } elseif (!$error) { $error = 'Periksa kembali data buku.'; }
}
$gambarSaatIni = $buku['gambar'] ?? '';
$gambarPreview = $gambarSaatIni !== '' ? (preg_match('~^https?://~i', $gambarSaatIni) ? $gambarSaatIni : '../../assets/uploads/' . rawurlencode(basename($gambarSaatIni))) : '';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit Buku - Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light">
<main class="container py-5" style="max-width:760px"><div class="card shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Edit Buku</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data">
<div class="mb-3"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($_POST['judul'] ?? $buku['judul']) ?>" required></div>
<div class="mb-3"><label class="form-label">Penulis</label><input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($_POST['penulis'] ?? $buku['penulis']) ?>" required></div>
<div class="mb-3"><label class="form-label">Kategori</label><select name="id_kategori" class="form-select" required><option value="">Pilih Kategori</option><?php foreach ($kategori_list as $k): ?><option value="<?= (int)$k['id'] ?>" <?= (($k['id'] == ($_POST['id_kategori'] ?? $buku['id_kategori'])) ? 'selected' : '') ?>><?= htmlspecialchars($k['nama_kategori']) ?></option><?php endforeach; ?></select><div class="form-text"><a href="../kategori.php">Kelola kategori</a></div></div>
<div class="row"><div class="col-md-6 mb-3"><label class="form-label">Harga (Rp)</label><input type="number" min="0" name="harga" class="form-control" value="<?= htmlspecialchars($_POST['harga'] ?? $buku['harga']) ?>" required></div><div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stok" class="form-control" value="<?= htmlspecialchars($_POST['stok'] ?? $buku['stok']) ?>" required></div></div>
<div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? $buku['deskripsi']) ?></textarea></div>
<?php if ($gambarPreview): ?><div class="mb-3"><label class="form-label d-block">Gambar Saat Ini</label><img src="<?= htmlspecialchars($gambarPreview) ?>" alt="Cover buku" style="max-width:130px;max-height:170px;object-fit:cover" class="rounded border"></div><?php endif; ?>
<div class="mb-3"><label class="form-label">Ganti dengan Link Gambar (opsional)</label><input type="url" name="gambar_url" class="form-control" placeholder="https://contoh.com/cover-buku.jpg"><div class="form-text">Kosongkan jika ingin tetap memakai gambar saat ini.</div></div>
<div class="mb-4"><label class="form-label">Atau Upload Gambar Baru</label><input type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"><div class="form-text">JPG, PNG, GIF, WEBP. Maksimal 5 MB. Pilih salah satu metode untuk mengganti gambar.</div></div>
<button type="submit" class="btn btn-primary">Simpan Perubahan</button> <a href="index.php" class="btn btn-outline-secondary">Batal</a></form></div></div></main></body></html>
