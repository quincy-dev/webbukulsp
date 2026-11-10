<?php
// Proses login pengguna.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && // Memeriksa password dengan hash yang tersimpan.
password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        // Mengarahkan pengguna ke halaman tujuan.
header('Location: ' . (($user['role'] ?? '') === 'admin' ? 'admin/index.php' : 'index.php'));
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="card shadow border-0 auth-card">
            <div class="card-body p-4 p-md-5">
                <h2 class="text-center mb-4">Masuk</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username atau Email</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
                <div class="text-center mt-3 small">
                    Belum punya akun? <a href="register.php">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </div>
<script src="assets/js/password-toggle.js"></script>
</body>
</html>
