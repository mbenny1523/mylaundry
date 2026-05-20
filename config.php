<?php
/**
 * MyLaundry - Database Configuration
 * Koneksi PDO ke database db_laundry
 * 
 * PETUNJUK HOSTING INFINITYFREE:
 * 1. Buka InfinityFree Control Panel → MySQL Databases
 * 2. Buat database baru, catat: hostname, username, password, nama database
 * 3. Ganti nilai di bagian "INFINITYFREE" di bawah ini
 * 4. Ubah $environment = 'production';
 */

// ============================================
// PILIH ENVIRONMENT: 'local' atau 'production'
// ============================================
$environment = 'local';

if ($environment === 'production') {
    // =============================================
    // INFINITYFREE - Isi dengan data dari cpanel
    // =============================================
    $host     = 'sql313.infinityfree.com';   // Ganti dengan MySQL hostname dari InfinityFree
    $dbname   = 'if0_12345678_db_laundry';   // Ganti dengan nama database dari InfinityFree
    $username = 'if0_12345678';              // Ganti dengan username database dari InfinityFree
    $password = 'password_anda';             // Ganti dengan password database dari InfinityFree
} else {
    // =============================================
    // LOCAL (XAMPP)
    // =============================================
    $host     = 'localhost';
    $dbname   = 'db_laundry';
    $username = 'root';
    $password = '';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die('<div style="text-align:center;margin-top:50px;font-family:Inter,sans-serif;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>Pastikan MySQL sudah berjalan dan database <b>' . htmlspecialchars($dbname) . '</b> sudah dibuat.</p>
        <p style="color:#888;font-size:13px;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
        <hr style="margin:20px auto;width:300px;border-color:#ddd;">
        <p style="color:#aaa;font-size:12px;">Jika hosting di InfinityFree, pastikan:<br>
        1. Database sudah dibuat di Control Panel<br>
        2. Hostname, username, password sudah benar di config.php<br>
        3. Tabel sudah diimport via phpMyAdmin</p>
    </div>');
}

// Start session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
