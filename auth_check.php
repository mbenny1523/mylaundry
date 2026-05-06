<?php
/**
 * MyLaundry - Auth Check
 * Proteksi halaman — include di setiap halaman yang butuh login
 */

require_once __DIR__ . '/config.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/**
 * Helper functions untuk role checking
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isKasir() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}

function isOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
}

/**
 * Proteksi halaman khusus admin
 */
function adminOnly() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}
