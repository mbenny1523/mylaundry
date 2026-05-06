<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $id_outlet = intval($_POST['id_outlet'] ?? 0);
    $role = $_POST['role'] ?? 'kasir';

    // Admin tidak perlu memilih outlet, auto-assign outlet pertama
    if ($role === 'admin' && $id_outlet === 0) {
        $firstOutlet = $pdo->query("SELECT id FROM tb_outlet ORDER BY id LIMIT 1")->fetchColumn();
        $id_outlet = $firstOutlet ?: 1;
    }

    if (empty($nama) || empty($username) || empty($password) || empty($confirm)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($role !== 'admin' && $id_outlet === 0) {
        $error = 'Pilih outlet terlebih dahulu!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (!in_array($role, ['admin', 'kasir', 'owner'])) {
        $error = 'Role tidak valid!';
    } else {
        // Cek username sudah dipakai
        $check = $pdo->prepare("SELECT id FROM tb_user WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'Username sudah digunakan!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO tb_user (nama, username, password, id_outlet, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $username, $hash, $id_outlet, $role]);
            $success = 'Registrasi berhasil! Silakan login.';
        }
    }
}

// Ambil data outlet untuk dropdown
$outlets = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MyLaundry</title>
    <meta name="description" content="Daftar akun baru di MyLaundry">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: #0f172a;
            overflow-y: auto;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(6,182,212,0.12), transparent 70%);
            top: -200px; left: -200px;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(59,130,246,0.1), transparent 70%);
            bottom: -200px; right: -100px;
            border-radius: 50%;
            animation: float 10s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-container {
            position: relative; z-index: 10;
            width: 100%; max-width: 480px;
            padding: 20px; margin: 40px 0;
            animation: slideUp 0.8s ease-out;
        }
        .register-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }
        .brand { text-align: center; margin-bottom: 30px; }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 28px; color: #fff;
            box-shadow: 0 8px 24px rgba(6,182,212,0.3);
        }
        .brand h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px; font-weight: 700;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand p { color: #64748b; font-size: 14px; margin-top: 4px; }
        .form-floating { margin-bottom: 14px; }
        .form-floating .form-control,
        .form-floating .form-select {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            color: #e2e8f0;
            height: 54px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-floating .form-control:focus,
        .form-floating .form-select:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
            color: #f1f5f9;
        }
        .form-floating label { color: #64748b; }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select ~ label {
            color: #3b82f6;
        }
        .form-select option { background: #1e293b; color: #e2e8f0; }
        .btn-register {
            width: 100%; height: 50px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            border: none; border-radius: 14px;
            color: #fff; font-weight: 600; font-size: 15px;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6,182,212,0.4);
            color: #fff;
        }
        .alert { border-radius: 12px; font-size: 14px; padding: 12px 16px; }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #6ee7b7; }
        .login-link { text-align: center; margin-top: 20px; color: #64748b; font-size: 14px; }
        .login-link a { color: #06b6d4; text-decoration: none; font-weight: 500; }
        .login-link a:hover { color: #22d3ee; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Daftar Akun</h1>
                <p>Buat akun baru untuk MyLaundry</p>
            </div>

            <?php if ($success): ?>
                <!-- Success Screen -->
                <div style="text-align:center;padding:20px 0;">
                    <div style="width:80px;height:80px;background:rgba(16,185,129,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;color:#34d399;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:700;color:#f1f5f9;margin-bottom:8px;">Registrasi Berhasil!</h2>
                    <p style="color:#94a3b8;font-size:14px;margin-bottom:28px;">Silakan masuk ke menu login untuk menggunakan akun Anda.</p>
                    <a href="login.php" class="btn btn-register" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Login
                    </a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Lengkap" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                        <label for="nama"><i class="fas fa-id-card me-2"></i>Nama Lengkap</label>
                    </div>

                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                        <label for="confirm_password"><i class="fas fa-lock me-2"></i>Konfirmasi Password</label>
                    </div>

                    <div class="form-floating">
                        <select class="form-select" id="id_outlet" name="id_outlet" required>
                            <option value="" disabled <?= empty($_POST['id_outlet']) ? 'selected' : '' ?>>Pilih outlet...</option>
                            <?php foreach ($outlets as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= (($_POST['id_outlet'] ?? '') == $o['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($o['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="id_outlet"><i class="fas fa-store me-2"></i>Outlet</label>
                    </div>

                    <div class="form-floating">
                        <select class="form-select" id="role" name="role" required>
                            <option value="kasir" <?= (($_POST['role'] ?? '') === 'kasir') ? 'selected' : '' ?>>Kasir</option>
                            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="owner" <?= (($_POST['role'] ?? '') === 'owner') ? 'selected' : '' ?>>Owner</option>
                        </select>
                        <label for="role"><i class="fas fa-shield-alt me-2"></i>Role</label>
                    </div>

                    <button type="submit" class="btn btn-register mt-2">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </button>
                </form>

                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('role');
        const outletSelect = document.getElementById('id_outlet');
        const outletWrapper = outletSelect.closest('.form-floating');

        function toggleOutlet() {
            if (roleSelect.value === 'admin') {
                outletSelect.disabled = true;
                outletSelect.removeAttribute('required');
                outletSelect.value = '';
                outletWrapper.style.opacity = '0.4';
                outletWrapper.style.pointerEvents = 'none';
            } else {
                outletSelect.disabled = false;
                outletSelect.setAttribute('required', 'required');
                outletWrapper.style.opacity = '1';
                outletWrapper.style.pointerEvents = 'auto';
            }
        }

        roleSelect.addEventListener('change', toggleOutlet);
        toggleOutlet(); // run on page load
    </script>
</body>
</html>
