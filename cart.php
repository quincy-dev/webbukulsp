<?php
// Menampilkan isi keranjang dan jumlah belanja.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
$currentUser=$_SESSION['user']??null;
if(!$currentUser){// Mengarahkan pengguna ke halaman tujuan.
header('Location: login.php');exit;}
$cart=// Data keranjang disimpan sementara di session.
$_SESSION['cart']??[]; $grandTotal=0;
foreach($cart as $item){$grandTotal+=(float)$item['harga']*(int)$item['jumlah'];}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Keranjang - BookStore</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__.'/includes/navbar.php';?><main class="container py-5"><h2 class="fw-bold mb-4">Keranjang Belanja</h2>
<?php if(!$cart):?><div class="alert alert-info">Keranjang masih kosong. <a href="books.php">Cari buku</a>.</div><?php else:?>
<div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th>Buku</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($cart as $bookId=>$item):$subtotal=(float)$item['harga']*(int)$item['jumlah'];?><tr><td><?=htmlspecialchars($item['judul'])?></td><td>Rp <?=number_format((float)$item['harga'],0,',','.')?></td><td><?=(int)$item['jumlah']?></td><td>Rp <?=number_format($subtotal,0,',','.')?></td><td><form method="post" action="remove_from_cart.php"><input type="hidden" name="book_id" value="<?=(int)$bookId?>"><button class="btn btn-outline-danger btn-sm">Hapus</button></form></td></tr><?php endforeach;?>
</tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th colspan="2">Rp <?=number_format($grandTotal,0,',','.')?></th></tr></tfoot></table></div><div class="d-flex justify-content-between"><a href="books.php" class="btn btn-outline-secondary">Lanjut Belanja</a><a href="checkout.php" class="btn btn-success">Checkout</a></div><?php endif;?></main></body></html>
