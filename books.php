<?php
// Menampilkan daftar buku dan fitur pencarian.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/image.php';
$search = trim($_GET['search'] ?? '');
$perPage = 8;
$page = max(1, (int)($_GET['page'] ?? 1));
$pattern = '%' . $search . '%';
if ($search !== '') {
    $count = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT COUNT(*) total FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.judul LIKE ? OR b.penulis LIKE ? OR k.nama_kategori LIKE ?");
    $count->bind_param('sss', $pattern, $pattern, $pattern); $count->execute();
    $totalBooks = (int)$count->get_result()->fetch_assoc()['total'];
} else {
    $totalBooks = (int)($conn->query("SELECT COUNT(*) total FROM buku")->fetch_assoc()['total'] ?? 0);
}
$totalPages = max(1, (int)ceil($totalBooks/$perPage)); $page = min($page,$totalPages); $offset=($page-1)*$perPage;
if ($search !== '') {
    $stmt=$conn->prepare("SELECT b.*,k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.judul LIKE ? OR b.penulis LIKE ? OR k.nama_kategori LIKE ? ORDER BY b.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('sssii',$pattern,$pattern,$pattern,$perPage,$offset);
} else {
    $stmt=$conn->prepare("SELECT b.*,k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id ORDER BY b.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii',$perPage,$offset);
}
$stmt->execute(); $books=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Koleksi Buku - BookStore</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__.'/includes/navbar.php'; ?>
<main class="container py-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h2 class="fw-bold mb-1">Koleksi Buku</h2><p class="text-muted mb-0">Temukan buku yang ingin kamu baca.</p></div>
<form method="get" class="d-flex gap-2"><input class="form-control" name="search" value="<?=htmlspecialchars($search)?>" placeholder="Cari buku atau penulis..."><button class="btn btn-primary">Cari</button><?php if($search!==''):?><a class="btn btn-outline-secondary" href="books.php">Reset</a><?php endif;?></form></div>
<?php if(!$books):?><div class="alert alert-info">Buku tidak ditemukan.</div><?php else:?><div class="row g-4">
<?php foreach($books as $book): $image=bookstoreImageSrc($book['gambar'] ?? '');?>
<div class="col-sm-6 col-lg-3"><div class="card h-100 shadow-sm book-card"><img src="<?=htmlspecialchars($image)?>" class="card-img-top" alt="<?=htmlspecialchars($book['judul'])?>" style="height:230px;object-fit:cover"><div class="card-body d-flex flex-column"><h5><?=htmlspecialchars($book['judul'])?></h5><p class="text-muted small mb-1"><?=htmlspecialchars($book['penulis'])?></p><p class="small text-muted"><?=htmlspecialchars($book['nama_kategori']??'Tanpa kategori')?></p><strong class="text-danger mt-auto">Rp <?=number_format((float)$book['harga'],0,',','.')?></strong><a class="btn btn-primary w-100 mt-3" href="add_to_cart.php?id=<?=(int)$book['id']?>">Tambah ke Keranjang</a></div></div></div>
<?php endforeach;?></div><?php endif;?>
<?php if($totalPages>1):?><nav class="mt-4"><ul class="pagination justify-content-center"><?php for($i=1;$i<=$totalPages;$i++):?><li class="page-item <?=$i===$page?'active':''?>"><a class="page-link" href="?page=<?=$i?>&search=<?=urlencode($search)?>"><?=$i?></a></li><?php endfor;?></ul></nav><?php endif;?>
</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script></body></html>
