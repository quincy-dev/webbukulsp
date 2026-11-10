<?php
// Proses checkout dengan metode pembayaran saat barang diterima (COD).

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
$user=$_SESSION['user']??null;
if(!$user){// Mengarahkan pengguna ke halaman tujuan.
header('Location: login.php');exit;}
$cart=// Data keranjang disimpan sementara di session.
$_SESSION['cart']??[];
if(!$cart){header('Location: cart.php');exit;}

/* Tabel transaksi dibuat otomatis agar instalasi sederhana. */
$conn->query("CREATE TABLE IF NOT EXISTS pesanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 nama_penerima VARCHAR(120) NOT NULL,
 telepon VARCHAR(30) NOT NULL,
 alamat TEXT NOT NULL,
 catatan TEXT NULL,
 total DECIMAL(12,2) NOT NULL DEFAULT 0,
 status VARCHAR(30) NOT NULL DEFAULT 'Menunggu',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
$items=[];$total=0;$error='';
foreach($cart as $bookId=>$item){
 $id=(int)$bookId;$stmt=// Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("SELECT id,judul,harga,stok FROM buku WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$book=$stmt->get_result()->fetch_assoc();
 if(!$book || (int)$book['stok']<(int)$item['jumlah']){$error='Stok buku '.htmlspecialchars($item['judul']).' tidak mencukupi. Silakan perbarui keranjang.';break;}
 $qty=(int)$item['jumlah'];$subtotal=(float)$book['harga']*$qty;$total+=$subtotal;$items[]=['id'=>$id,'judul'=>$book['judul'],'harga'=>$book['harga'],'qty'=>$qty,'subtotal'=>$subtotal];
}
if($_SERVER['REQUEST_METHOD']==='POST' && !$error){
 $nama=trim($_POST['nama_penerima']??'');$telepon=trim($_POST['telepon']??'');$alamat=trim($_POST['alamat']??'');$catatan=trim($_POST['catatan']??'');
 if($nama===''||$telepon===''||$alamat===''){$error='Nama penerima, nomor telepon, dan alamat wajib diisi.';}
 else{
  try{
   $conn->begin_transaction();
   $uid=(int)$user['id'];
   $stmt=$conn->prepare("INSERT INTO pesanan(user_id,nama_penerima,telepon,alamat,catatan,total) VALUES(?,?,?,?,?,?)");
   $stmt->bind_param('issssd',$uid,$nama,$telepon,$alamat,$catatan,$total);$stmt->execute();$orderId=$conn->insert_id;
   foreach($items as $it){
    $lock=$conn->prepare("SELECT stok FROM buku WHERE id=? FOR UPDATE");$lock->bind_param('i',$it['id']);$lock->execute();$stock=$lock->get_result()->fetch_assoc();
    if(!$stock||(int)$stock['stok']<$it['qty'])throw new Exception('Stok buku berubah. Silakan coba checkout kembali.');
    $upd=$conn->prepare("UPDATE buku SET stok=stok-? WHERE id=?");$upd->bind_param('ii',$it['qty'],$it['id']);$upd->execute();
    $det=$conn->prepare("INSERT INTO detail_pesanan(pesanan_id,buku_id,judul_buku,harga,jumlah,subtotal) VALUES(?,?,?,?,?,?)");
    $det->bind_param('iisdid',$orderId,$it['id'],$it['judul'],$it['harga'],$it['qty'],$it['subtotal']);$det->execute();
   }
   $conn->commit();unset($_SESSION['cart']);header('Location: checkout.php?success='.$orderId);exit;
  }catch(Throwable $e){$conn->rollback();$error=$e->getMessage();}
 }
}
$success=filter_input(INPUT_GET,'success',FILTER_VALIDATE_INT);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Checkout - BookStore</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__.'/includes/navbar.php';?><main class="container py-5" style="max-width:900px">
<?php if($success):?><div class="text-center card border-0 shadow-sm p-5"><i class="bi bi-check-circle text-success display-3"></i><h2 class="mt-3">Pesanan berhasil dibuat!</h2><p class="text-muted">Nomor pesanan: <strong>#<?=$success?></strong>. Admin akan memproses pesananmu.</p><a href="books.php" class="btn btn-primary">Kembali ke koleksi</a></div>
<?php else:?><h2 class="fw-bold mb-4">Checkout</h2><?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
<div class="row g-4"><div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h5 class="mb-3">Informasi Penerima</h5><form method="post">
<div class="mb-3"><label class="form-label">Nama penerima</label><input class="form-control" name="nama_penerima" value="<?=htmlspecialchars($_POST['nama_penerima']??$user['nama_lengkap']??'')?>" required></div>
<div class="mb-3"><label class="form-label">Nomor telepon</label><input class="form-control" name="telepon" value="<?=htmlspecialchars($_POST['telepon']??'')?>" required></div>
<div class="mb-3"><label class="form-label">Alamat lengkap</label><textarea class="form-control" name="alamat" rows="3" required><?=htmlspecialchars($_POST['alamat']??'')?></textarea></div>
<div class="mb-3"><label class="form-label">Catatan (opsional)</label><textarea class="form-control" name="catatan" rows="2"><?=htmlspecialchars($_POST['catatan']??'')?></textarea></div><button class="btn btn-success w-100">Buat Pesanan</button></form></div></div></div>
<div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h5>Ringkasan Pesanan</h5><?php foreach($items as $it):?><div class="d-flex justify-content-between gap-2 py-2 border-bottom"><div><?=htmlspecialchars($it['judul'])?><div class="small text-muted"><?=$it['qty']?> × Rp <?=number_format((float)$it['harga'],0,',','.')?></div></div><strong>Rp <?=number_format($it['subtotal'],0,',','.')?></strong></div><?php endforeach;?><div class="d-flex justify-content-between pt-3 fs-5"><strong>Total</strong><strong class="text-danger">Rp <?=number_format($total,0,',','.')?></strong></div></div></div></div></div><?php endif;?></main></body></html>
