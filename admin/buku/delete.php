<?php
// Menghapus data buku dari database.
 
// admin/buku/delete.php 
// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once '../../config/database.php';  
if (empty($_SESSION['user']) || (($_SESSION['user']['role'] ?? '') !== 'admin')) {
    http_response_code(403);
    exit('Akses ditolak. Silakan masuk menggunakan akun admin.');
}
$id = $_GET['id'];  
$query = "DELETE FROM buku WHERE id = ?"; 
$stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare($query); 
$stmt->bind_param("i", $id);  
if ($stmt->execute()) {     // Mengarahkan pengguna ke halaman tujuan.
header('Location: index.php');     exit(); } ?>

