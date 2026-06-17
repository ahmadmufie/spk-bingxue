<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    redirect($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php');
}

$error = '';
$mode  = isset($_GET['mode']) ? $_GET['mode'] : 'login'; // login or register

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $name     = sanitize($db, $_POST['name'] ?? '');
        $email    = sanitize($db, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Semua field harus diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } elseif ($password !== $confirm) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Email sudah terdaftar.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt2->bind_param("sss", $name, $email, $hash);
                if ($stmt2->execute()) {
                    $_SESSION['flash'] = ['msg' => 'Registrasi berhasil! Silakan login.', 'type' => 'success'];
                    redirect('login.php');
                } else {
                    $error = 'Gagal mendaftar. Silakan coba lagi.';
                }
            }
        }
        $mode = 'register';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email    = sanitize($db, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi.';
        } else {
            $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['name']    = $row['name'];
                    $_SESSION['email']   = $row['email'];
                    $_SESSION['role']    = $row['role'];
                    redirect($row['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php');
                } else {
                    $error = 'Password salah.';
                }
            } else {
                $error = 'Email tidak ditemukan.';
            }
        }
    }
}

// Display flash from session
$flashMsg = '';
if (isset($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    $flashMsg = '<div class="alert alert-' . $f['type'] . '">' . htmlspecialchars($f['msg']) . '</div>';
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= APP_NAME ?> - Login</title>
<link rel="stylesheet" href="assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
<?= $flashMsg ?>
<div class="auth-card">
    <div class="auth-logo">
        <div class="logo-badge">B</div>
        <h1><?= APP_NAME ?></h1>
        <p>Sistem Pendukung Keputusan Rekrutmen</p>
    </div>

    <div class="auth-tabs">
        <a href="login.php" class="auth-tab <?= $mode === 'login' ? 'active' : '' ?>">Masuk</a>
        <a href="login.php?mode=register" class="auth-tab <?= $mode === 'register' ? 'active' : '' ?>">Daftar</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="position:static;transform:none;margin-bottom:18px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($mode === 'login'): ?>
    <form method="POST">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Masuk ke Sistem</button>
        <p class="text-center text-sm text-muted mt-4">
            Admin: <strong>admin@bingxue.com</strong> / <strong>password</strong>
        </p>
    </form>
    <?php else: ?>
    <form method="POST">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" placeholder="Nama lengkap Anda" required>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="form-group">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Buat Akun</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
