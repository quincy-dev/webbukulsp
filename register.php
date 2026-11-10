<?php
// Form dan proses pendaftaran akun baru.

// Memanggil file pendukung yang dibutuhkan halaman ini.
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $check = // Query disiapkan terlebih dahulu agar input lebih aman.
$conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            $hashed = // Password disimpan dalam bentuk hash, bukan teks biasa.
password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, email, password, nama_lengkap, role) VALUES (?, ?, ?, ?, "user")');
            $stmt->bind_param('ssss', $username, $email, $hashed, $nama_lengkap);

            if ($stmt->execute()) {
                $user = [
                    'id' => $conn->insert_id,
                    'username' => $username,
                    'email' => $email,
                    'nama_lengkap' => $nama_lengkap,
                    'role' => 'user'
                ];
                $_SESSION['user'] = $user;
                // Mengarahkan pengguna ke halaman tujuan.
header('Location: index.php');
                exit;
            }

            $error = 'Registrasi gagal. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="card shadow border-0 auth-card">
            <div class="card-body p-4 p-md-5">
                <h2 class="text-center mb-4">Daftar</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Daftar</button>
                </form>
                <div class="text-center mt-3 small">
                    Sudah punya akun? <a href="login.php">Masuk</a>
                </div>
            </div>
        </div>
    </div>
<script src="assets/js/password-toggle.js"></script>
</body>
</html>
