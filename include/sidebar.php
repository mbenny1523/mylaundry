<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar no-print" id="sidebar">
    <div class="sidebar-brand">
        <div class="icon-box"><i class="fas fa-tshirt"></i></div>
        <h2>MyLaundry</h2>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Menu Utama</div>
        <a href="index.php" class="menu-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="member.php" class="menu-item <?= $currentPage === 'member.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Data Member
        </a>
        <a href="outlet.php" class="menu-item <?= $currentPage === 'outlet.php' ? 'active' : '' ?>">
            <i class="fas fa-store"></i> Data Outlet
        </a>

        <?php if (isAdmin() || isOwner()): ?>
        <a href="pengguna.php" class="menu-item <?= $currentPage === 'pengguna.php' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i> Data Pengguna
        </a>
        <?php endif; ?>

        <div class="menu-label">Transaksi</div>
        <a href="transaksi.php" class="menu-item <?= $currentPage === 'transaksi.php' ? 'active' : '' ?>">
            <i class="fas fa-cash-register"></i> Entri Transaksi
        </a>

        <?php if (isAdmin() || isOwner()): ?>
        <div class="menu-label">Analitik</div>
        <a href="laporan.php" class="menu-item <?= $currentPage === 'laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Laporan
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?></div>
            <div class="user-details">
                <div class="name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                <div class="role"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- Main Content Wrapper -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="hamburger" id="hamburgerBtn"><i class="fas fa-bars"></i></button>
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline" style="font-size:13px;color:var(--text-muted)">
                <i class="fas fa-store me-1"></i><?= htmlspecialchars($_SESSION['outlet_nama'] ?? '') ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:13px;">
                <i class="fas fa-sign-out-alt me-1"></i>Keluar
            </a>
        </div>
    </div>
    <div class="content-area">
