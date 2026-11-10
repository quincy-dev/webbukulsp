<?php
// Menambahkan buku pilihan ke keranjang session.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['user'])) {
    // Mengarahkan pengguna ke halaman tujuan.
header('Location: login.php');
    exit;
}

$bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bookId || $bookId < 1) {
    header('Location: books.php');
    exit;
}

$stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare('SELECT id, judul, harga, stok FROM buku WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $bookId);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book || (int) $book['stok'] < 1) {
    header('Location: books.php');
    exit;
}

if (!isset(// Data keranjang disimpan sementara di session.
$_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$currentQuantity = $_SESSION['cart'][$bookId]['jumlah'] ?? 0;
if ($currentQuantity < (int) $book['stok']) {
    $_SESSION['cart'][$bookId] = [
        'judul' => $book['judul'],
        'harga' => (int) $book['harga'],
        'jumlah' => $currentQuantity + 1,
    ];
}

header('Location: cart.php');
exit;
