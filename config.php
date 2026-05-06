<?php
/**
 * MyLaundry - Database Configuration
 * Koneksi PDO ke database db_laundry
 */

$host = 'localhost';
$dbname = 'db_laundry';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die('<div style="text-align:center;margin-top:50px;font-family:Inter,sans-serif;">
        <h2>⚠️ Koneksi Database Gagal</h2>
        <p>Pastikan MySQL sudah berjalan dan database <b>db_laundry</b> sudah dibuat.</p>
        <p style="color:#888;font-size:13px;">' . $e->getMessage() . '</p>
    </div>');
}

// Start session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
