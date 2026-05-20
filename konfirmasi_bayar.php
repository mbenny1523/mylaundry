<?php
$pageTitle = 'Konfirmasi Pembayaran';
require_once 'auth_check.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: transaksi.php'); exit; }

// Handle confirm payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uang_muka = intval($_POST['uang_muka'] ?? 0);

    // Get current transaction to recalculate
    $cur = $pdo->prepare("SELECT biaya FROM tb_transaksi WHERE id = ?");
    $cur->execute([$id]);
    $curData = $cur->fetch();
    $kembalian = max(0, $uang_muka - $curData['biaya']);

    $stmt = $pdo->prepare("UPDATE tb_transaksi SET pembayaran = 'dibayar', tgl_bayar = ?, uang_muka = ?, kembalian = ? WHERE id = ?");
    $stmt->execute([date('Y-m-d'), $uang_muka, $kembalian, $id]);
    header("Location: cetak_invoice.php?id=$id&paid=1"); exit;
}

$trx = $pdo->prepare("SELECT t.*, m.nama as member_nama, m.kode_member, m.telp as member_telp, 
    o.nama as outlet_nama, o.alamat as outlet_alamat, o.telp as outlet_telp, 
    u.nama as user_nama, p.nama_paket, p.harga_per_kg
    FROM tb_transaksi t 
    LEFT JOIN tb_member m ON t.id_member = m.id 
    LEFT JOIN tb_outlet o ON t.id_outlet = o.id 
    LEFT JOIN tb_user u ON t.id_user = u.id 
    LEFT JOIN tb_paket p ON t.id_paket = p.id
    WHERE t.id = ?");
$trx->execute([$id]);
$trx = $trx->fetch();
if (!$trx) { header('Location: transaksi.php'); exit; }

include 'include/header.php';
include 'include/sidebar.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-card fade-in">
            <div class="card-title"><i class="fas fa-file-invoice-dollar" style="color:var(--green)"></i> Detail Transaksi</div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div style="padding:16px;background:var(--bg-input);border-radius:12px;">
                        <small style="color:var(--text-muted)">Invoice</small>
                        <div style="font-size:18px;font-weight:700;color:var(--cyan)"><?= htmlspecialchars($trx['kode_invoice']) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="padding:16px;background:var(--bg-input);border-radius:12px;">
                        <small style="color:var(--text-muted)">Status Pembayaran</small>
                        <div><span class="badge-status <?= $trx['pembayaran'] === 'dibayar' ? 'badge-dibayar' : 'badge-belum' ?>" style="font-size:14px"><?= $trx['pembayaran'] === 'dibayar' ? '✅ Lunas' : '⏳ Belum Lunas' ?></span></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table-dark-custom">
                    <tbody>
                        <tr><td style="color:var(--text-muted);width:40%">Member</td><td><?= htmlspecialchars($trx['kode_member'] . ' - ' . $trx['member_nama']) ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Outlet</td><td><?= htmlspecialchars($trx['outlet_nama']) ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Paket</td><td><?= htmlspecialchars($trx['nama_paket']) ?> — Rp <?= number_format($trx['harga_per_kg'], 0, ',', '.') ?>/kg</td></tr>
                        <tr><td style="color:var(--text-muted)">Berat</td><td><?= number_format($trx['berat'], 1) ?> kg</td></tr>
                        <tr><td style="color:var(--text-muted)">Subtotal</td><td>Rp <?= number_format($trx['subtotal'], 0, ',', '.') ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Pajak & Tip</td><td>Rp <?= number_format($trx['pajak'], 0, ',', '.') ?></td></tr>
                        <tr><td style="color:var(--text-muted);font-weight:700">Total Biaya</td><td style="font-size:18px;font-weight:700;color:var(--green)">Rp <?= number_format($trx['biaya'], 0, ',', '.') ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Uang Muka</td><td>Rp <?= number_format($trx['uang_muka'], 0, ',', '.') ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Kembalian</td><td style="color:var(--cyan);font-weight:600">Rp <?= number_format($trx['kembalian'], 0, ',', '.') ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Tanggal</td><td><?= date('d F Y', strtotime($trx['tgl'])) ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Batas Waktu</td><td><?= date('d F Y', strtotime($trx['batas_waktu'])) ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Status</td><td><span class="badge-status badge-<?= $trx['status'] ?>"><?= ucfirst($trx['status']) ?></span></td></tr>
                        <tr><td style="color:var(--text-muted)">Kasir</td><td><?= htmlspecialchars($trx['user_nama']) ?></td></tr>
                        <tr><td style="color:var(--text-muted)">Keterangan</td><td><?= htmlspecialchars($trx['keterangan'] ?? '-') ?></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 justify-content-end flex-wrap">
                <a href="transaksi.php" class="btn btn-secondary" style="border-radius:10px"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
                <a href="cetak_invoice.php?id=<?= $trx['id'] ?>" target="_blank" class="btn btn-primary-custom"><i class="fas fa-print me-2"></i>Cetak Invoice</a>
                <?php if ($trx['pembayaran'] === 'belum_dibayar' && (isAdmin() || isOwner())): ?>
                <button class="btn btn-success-custom" data-bs-toggle="modal" data-bs-target="#bayarModal">
                    <i class="fas fa-check-circle me-2"></i>Konfirmasi Bayar
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bayar -->
<?php if ($trx['pembayaran'] === 'belum_dibayar'): ?>
<div class="modal fade" id="bayarModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Konfirmasi Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
    <div class="modal-body">
        <p style="color:var(--text-secondary)">Total yang harus dibayar:</p>
        <h3 style="color:var(--green);font-weight:700;margin-bottom:20px">Rp <?= number_format($trx['biaya'], 0, ',', '.') ?></h3>
        <div class="mb-3">
            <label class="form-label-dark">Uang Muka / Bayar (Rp)</label>
            <input type="number" name="uang_muka" id="bayarUangMuka" class="form-control form-control-dark" min="0" value="<?= $trx['biaya'] ?>" required>
        </div>
        <div class="d-flex justify-content-between" style="font-size:15px;font-weight:600;">
            <span style="color:var(--text-primary)">Kembalian</span>
            <span id="bayarKembalian" style="color:var(--cyan)">Rp 0</span>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success-custom">Konfirmasi Lunas</button></div>
    </form>
</div></div></div>
<script>
document.getElementById('bayarUangMuka').addEventListener('input', function() {
    const total = <?= $trx['biaya'] ?>;
    const uang = parseInt(this.value) || 0;
    const kembalian = Math.max(0, uang - total);
    document.getElementById('bayarKembalian').textContent = 'Rp ' + kembalian.toLocaleString('id-ID');
});
</script>
<?php endif; ?>

<?php include 'include/footer.php'; ?>
