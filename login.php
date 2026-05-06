<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, o.nama as outlet_nama FROM tb_user u JOIN tb_outlet o ON u.id_outlet = o.id WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['id_outlet'] = $user['id_outlet'];
            $_SESSION['outlet_nama'] = $user['outlet_nama'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyLaundry</title>
    <meta name="description" content="Login ke sistem manajemen MyLaundry">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(59,130,246,0.15), transparent 70%);
            top: -200px; right: -200px;
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,0.1), transparent 70%);
            bottom: -200px; left: -100px;
            border-radius: 50%;
            animation: float 10s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
            animation: slideUp 0.8s ease-out;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }
        .brand {
            text-align: center;
            margin-bottom: 36px;
        }
        .brand-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px; color: #fff;
            box-shadow: 0 8px 24px rgba(59,130,246,0.3);
            transition: transform 0.3s;
        }
        .brand-icon:hover { transform: scale(1.05) rotate(-3deg); }
        .brand h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px; font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand p { color: #64748b; font-size: 14px; margin-top: 4px; }
        .form-floating { margin-bottom: 16px; }
        .form-floating .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            color: #e2e8f0;
            height: 56px;
            padding: 16px 20px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-floating .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
            color: #f1f5f9;
        }
        .form-floating label {
            color: #64748b;
            padding: 16px 20px;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #3b82f6;
        }
        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.3px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59,130,246,0.4);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }
        .btn-login:active { transform: translateY(0); }
        .alert-danger {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 14px;
            padding: 12px 16px;
        }
        .register-link {
            text-align: center;
            margin-top: 24px;
            color: #64748b;
            font-size: 14px;
        }
        .register-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .register-link a:hover { color: #60a5fa; }
        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            z-index: 5;
            cursor: pointer;
            transition: color 0.2s;
        }
        .input-icon:hover { color: #94a3b8; }
        .input-wrapper { position: relative; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-tshirt"></i>
                </div>
                <h1>MyLaundry</h1>
                <p>Sistem Manajemen Laundry</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                </div>

                <div class="form-floating input-wrapper">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <span class="input-icon" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>

                <button type="submit" class="btn btn-login mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>

            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
