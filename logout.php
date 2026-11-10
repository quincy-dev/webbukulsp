<?php
// Keluar dari akun dan menghapus session login.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
// Mengarahkan pengguna ke halaman tujuan.
header('Location: index.php');
exit;
