<?php
$pageTitle = 'Laporan Transaksi';
require_once 'auth_check.php';
if (!isAdmin() && !isOwner()) { header('Location: index.php'); exit; }

$tglMulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tglSelesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT t.*, m.nama as member_nama, m.kode_member, o.nama as outlet_nama, u.nama as user_nama, p.nama_paket
        FROM tb_transaksi t
        LEFT JOIN tb_member m ON t.id_member = m.id
        LEFT JOIN tb_outlet o ON t.id_outlet = o.id
        LEFT JOIN tb_user u ON t.id_user = u.id
        LEFT JOIN tb_paket p ON t.id_paket = p.id
        WHERE t.tgl BETWEEN ? AND ?
        ORDER BY t.tgl DESC, t.id DESC");
    $stmt->execute([$tglMulai, $tglSelesai]);
    $data = $stmt->fetchAll();
} catch (Exception $e) {
    $data = [];
}

try {
    $stmtOmzet = $pdo->prepare("SELECT COALESCE(SUM(biaya), 0) FROM tb_transaksi WHERE tgl BETWEEN ? AND ? AND pembayaran = 'dibayar'");
    $stmtOmzet->execute([$tglMulai, $tglSelesai]);
    $totalOmzet = $stmtOmzet->fetchColumn();
} catch (Exception $e) {
    $totalOmzet = 0;
}

include 'include/header.php';
include 'include/sidebar.php';
?>

<!-- Filter -->
<div class="glass-card mb-4 fade-in">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label-dark">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control form-control-dark" value="<?= $tglMulai ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label-dark">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control form-control-dark" value="<?= $tglSelesai ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary-custom w-100"><i class="fas fa-filter me-2"></i>Filter</button>
        </div>
    </form>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-6 fade-in-delay-1">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
            <div class="stat-value"><?= count($data) ?></div>
            <div class="stat-label">Total Transaksi</div>
        </div>
    </div>
    <div class="col-md-6 fade-in-delay-2">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
            <div class="stat-value">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></div>
            <div class="stat-label">Total Omzet (Lunas)</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="glass-card fade-in-delay-3">
    <div class="card-title"><i class="fas fa-table" style="color:var(--blue)"></i> Detail Transaksi (<?= date('d/m/Y', strtotime($tglMulai)) ?> — <?= date('d/m/Y', strtotime($tglSelesai)) ?>)</div>
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>No</th><th>Invoice</th><th>Member</th><th>Paket</th><th>Berat</th><th>Subtotal</th><th>Pajak</th><th>Total</th><th>Uang Muka</th><th>Kembalian</th><th>Bayar</th><th>Kasir</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code style="color:var(--cyan)"><?= htmlspecialchars($t['kode_invoice']) ?></code></td>
                    <td><?= htmlspecialchars($t['member_nama']) ?></td>
                    <td><?= htmlspecialchars($t['nama_paket']) ?></td>
                    <td><?= number_format($t['berat'], 1) ?> kg</td>
                    <td>Rp <?= number_format($t['subtotal'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($t['pajak'], 0, ',', '.') ?></td>
                    <td style="font-weight:600">Rp <?= number_format($t['biaya'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($t['uang_muka'], 0, ',', '.') ?></td>
                    <td style="color:var(--cyan)">Rp <?= number_format($t['kembalian'], 0, ',', '.') ?></td>
                    <td><span class="badge-status <?= $t['pembayaran'] === 'dibayar' ? 'badge-dibayar' : 'badge-belum' ?>"><?= $t['pembayaran'] === 'dibayar' ? 'Lunas' : 'Belum' ?></span></td>
                    <td><?= htmlspecialchars($t['user_nama']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($data)): ?>
                <tr><td colspan="12" style="text-align:center;color:var(--text-muted);padding:30px;">Tidak ada data pada rentang tanggal ini</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($data)): ?>
            <tfoot>
                <tr style="background:rgba(16,185,129,0.08)">
                    <td colspan="7" style="text-align:right;font-weight:700;color:var(--green)">Total Omzet (Lunas):</td>
                    <td colspan="5" style="font-size:18px;font-weight:700;color:var(--green)">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include 'include/footer.php'; ?>
