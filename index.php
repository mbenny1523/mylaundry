<?php
$pageTitle = 'Dashboard';
require_once 'auth_check.php';

// Summary data
try {
    $totalMhs = $pdo->query("SELECT COUNT(*) FROM tb_member")->fetchColumn();
    $totalOutlet = $pdo->query("SELECT COUNT(*) FROM tb_outlet")->fetchColumn();

    $today = date('Y-m-d');
    $stmtToday = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi WHERE tgl = ?");
    $stmtToday->execute([$today]);
    $trxToday = $stmtToday->fetchColumn();

    $stmtOmzet = $pdo->prepare("SELECT COALESCE(SUM(biaya),0) FROM tb_transaksi WHERE tgl = ? AND pembayaran = 'dibayar'");
    $stmtOmzet->execute([$today]);
    $omzetToday = $stmtOmzet->fetchColumn();

    // Recent transactions
    $recent = $pdo->query("SELECT t.*, m.nama as member_nama, o.nama as outlet_nama, 
        COALESCE(p.nama_paket, '-') as nama_paket
        FROM tb_transaksi t 
        JOIN tb_member m ON t.id_member = m.id 
        JOIN tb_outlet o ON t.id_outlet = o.id 
        LEFT JOIN tb_paket p ON t.id_paket = p.id
        ORDER BY t.id DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $totalMhs = 0;
    $totalOutlet = 0;
    $trxToday = 0;
    $omzetToday = 0;
    $recent = [];
}

include 'include/header.php';
include 'include/sidebar.php';
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3 fade-in-delay-1">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= number_format($totalMhs) ?></div>
            <div class="stat-label">Total Member</div>
        </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-delay-2">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-store"></i></div>
            <div class="stat-value"><?= number_format($totalOutlet) ?></div>
            <div class="stat-label">Total Outlet</div>
        </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-delay-3">
        <div class="stat-card yellow">
            <div class="stat-icon yellow"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= number_format($trxToday) ?></div>
            <div class="stat-label">Transaksi Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-lg-3 fade-in-delay-4">
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fas fa-wallet"></i></div>
            <div class="stat-value">Rp <?= number_format($omzetToday, 0, ',', '.') ?></div>
            <div class="stat-label">Omzet Hari Ini</div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="glass-card fade-in">
    <div class="card-title"><i class="fas fa-clock" style="color:var(--blue)"></i> Transaksi Terbaru</div>
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Member</th>
                    <th>Paket</th>
                    <th>Total</th>
                    <th>Uang Muka</th>
                    <th>Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi</td></tr>
                <?php else: ?>
                    <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><code style="color:var(--cyan)"><?= htmlspecialchars($r['kode_invoice']) ?></code></td>
                        <td><?= htmlspecialchars($r['member_nama']) ?></td>
                        <td><?= htmlspecialchars($r['nama_paket']) ?></td>
                        <td>Rp <?= number_format($r['biaya'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($r['uang_muka'], 0, ',', '.') ?></td>
                        <td><span class="badge-status <?= $r['pembayaran'] === 'dibayar' ? 'badge-dibayar' : 'badge-belum' ?>"><?= $r['pembayaran'] === 'dibayar' ? 'Lunas' : 'Belum' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'include/footer.php'; ?>
