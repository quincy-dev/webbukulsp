<?php
// Menghapus satu buku dari keranjang belanja.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Mengarahkan pengguna ke halaman tujuan.
header('Location: cart.php');
    exit;
}

$bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
if ($bookId && isset(// Data keranjang disimpan sementara di session.
$_SESSION['cart'][$bookId])) {
    unset($_SESSION['cart'][$bookId]);
}

header('Location: cart.php');
exit;
