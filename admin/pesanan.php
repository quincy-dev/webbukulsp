<?php
// Admin melihat dan mengubah status pesanan.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__.'/../config/database.php';
if(empty($_SESSION['user']) || ($_SESSION['user']['role']??'')!=='admin'){http_response_code(403);exit('Akses ditolak.');}
if(!$conn->query("SHOW TABLES LIKE 'pesanan'")->num_rows){$orders=[];}else{
 if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'],$_POST['status'])){$id=(int)$_POST['id'];$status=$_POST['status'];if(in_array($status,['Menunggu','Diproses','Dikirim','Selesai','Dibatalkan'],true)){$u=// Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare("UPDATE pesanan SET status=? WHERE id=?");$u->bind_param('si',$status,$id);$u->execute();}}
 $result=$conn->query("SELECT p.*,u.nama_lengkap FROM pesanan p LEFT JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC");$orders=$result->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pesanan - Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>
<nav class="navbar navbar-expand bg-body-tertiary shadow-sm"><div class="container"><a class="navbar-brand fw-bold" href="../index.php">BookStore Admin</a><div class="ms-auto d-flex gap-3"><a href="index.php" class="nav-link">Dashboard</a><a href="buku/index.php" class="nav-link">Kelola Buku</a></div></div></nav>
<main class="container py-5"><div class="d-flex justify-content-between align-items-center mb-4"><h2 class="fw-bold">Data Pesanan</h2><a class="btn btn-outline-secondary" href="index.php">Kembali</a></div>
<?php if(!$orders):?><div class="alert alert-info">Belum ada pesanan.</div><?php else:?><div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th>No.</th><th>Penerima</th><th>Kontak & Alamat</th><th>Total</th><th>Tanggal</th><th>Status</th></tr></thead><tbody><?php foreach($orders as $o):?><tr><td>#<?=$o['id']?></td><td><?=htmlspecialchars($o['nama_penerima'])?><div class="small text-muted"><?=htmlspecialchars($o['nama_lengkap']??'')?></div></td><td><?=htmlspecialchars($o['telepon'])?><div class="small text-muted"><?=nl2br(htmlspecialchars($o['alamat']))?></div></td><td>Rp <?=number_format((float)$o['total'],0,',','.')?></td><td><?=htmlspecialchars($o['created_at'])?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="id" value="<?=$o['id']?>"><select name="status" class="form-select form-select-sm"><?php foreach(['Menunggu','Diproses','Dikirim','Selesai','Dibatalkan'] as $st):?><option <?=$o['status']===$st?'selected':''?>><?=$st?></option><?php endforeach;?></select><button class="btn btn-sm btn-primary">Simpan</button></form></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></main></body></html>
