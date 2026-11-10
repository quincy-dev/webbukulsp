<?php
// Koneksi aplikasi ke database MySQL.

// Konfigurasi Database 
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'bookstore_db';  // Membuat koneksi 
$conn = new mysqli($host, $username, $password, $database);  // Cek koneksi 
if ($conn->connect_error) {
	die("Koneksi gagal: " . $conn->connect_error);
}  // Set charset 
$conn->set_charset("utf8mb4");
// Base URL 
define('BASE_URL', 'http://localhost/bookstore/');
// Start session only when one is not already active
if (session_status() === PHP_SESSION_NONE) {
	// Session dipakai untuk menyimpan status login dan keranjang.
session_start();
}
?>