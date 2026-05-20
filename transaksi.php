<?php
$pageTitle = 'Entri Transaksi';
require_once 'auth_check.php';

// Handle add transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    try {
        $kode = 'INV-' . date('Ymd') . '-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $id_member = intval($_POST['id_member'] ?? 0);
        $id_paket = intval($_POST['id_paket'] ?? 0);
        $berat = floatval($_POST['berat'] ?? 0);
        $uang_muka = intval($_POST['uang_muka'] ?? 0);
        $tgl = $_POST['tgl'] ?? date('Y-m-d');
        $batas_waktu = $_POST['batas_waktu'] ?? date('Y-m-d', strtotime('+3 days'));
        $keterangan = trim($_POST['keterangan'] ?? 'Menggunakan aplikasi MyLaundry');
        $pembayaran = $_POST['pembayaran'] ?? 'belum_dibayar';

        if ($id_member === 0) throw new Exception('Pilih member terlebih dahulu!');
        if ($id_paket === 0) throw new Exception('Pilih paket layanan!');
        if ($berat <= 0) throw new Exception('Berat harus lebih dari 0!');

        // Get paket price
        $paketStmt = $pdo->prepare("SELECT harga_per_kg FROM tb_paket WHERE id = ?");
        $paketStmt->execute([$id_paket]);
        $harga = $paketStmt->fetchColumn();
        if (!$harga) throw new Exception('Paket tidak ditemukan!');

        $subtotal = intval($harga * $berat);
        $pajak = 4000;
        $biaya = $subtotal + $pajak;
        $kembalian = max(0, $uang_muka - $biaya);

        // Jika uang muka >= biaya, otomatis lunas
        if ($uang_muka >= $biaya) {
            $pembayaran = 'dibayar';
        }

        if (empty($keterangan)) {
            $keterangan = 'Menggunakan aplikasi MyLaundry';
        }

        $stmt = $pdo->prepare("INSERT INTO tb_transaksi (id_outlet, kode_invoice, id_member, id_paket, berat, subtotal, pajak, biaya, uang_muka, kembalian, tgl, batas_waktu, tgl_bayar, status, pembayaran, keterangan, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'baru', ?, ?, ?)");
        $stmt->execute([
            $_SESSION['id_outlet'], $kode, $id_member, $id_paket,
            $berat, $subtotal, $pajak, $biaya, $uang_muka, $kembalian,
            $tgl, $batas_waktu,
            $pembayaran === 'dibayar' ? date('Y-m-d') : null,
            $pembayaran, $keterangan, $_SESSION['user_id']
        ]);
        $newId = $pdo->lastInsertId();
        header("Location: konfirmasi_bayar.php?id=$newId"); exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $stmt = $pdo->prepare("UPDATE tb_transaksi SET status=? WHERE id=?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header('Location: transaksi.php?msg=updated'); exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tb_transaksi WHERE id=?")->execute([$_GET['delete']]);
    header('Location: transaksi.php?msg=deleted'); exit;
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($filterStatus && in_array($filterStatus, ['baru', 'proses', 'selesai', 'diambil'])) {
    $where = ' WHERE t.status = ?';
    $params[] = $filterStatus;
}

try {
    $data = $pdo->prepare("SELECT t.*, m.nama as member_nama, m.kode_member, o.nama as outlet_nama, p.nama_paket
        FROM tb_transaksi t 
        LEFT JOIN tb_member m ON t.id_member = m.id 
        LEFT JOIN tb_outlet o ON t.id_outlet = o.id 
        LEFT JOIN tb_paket p ON t.id_paket = p.id $where
        ORDER BY t.id DESC");
    $data->execute($params);
    $data = $data->fetchAll();
} catch (Exception $e) {
    $data = [];
}

try {
    $members = $pdo->query("SELECT * FROM tb_member ORDER BY nama")->fetchAll();
} catch (Exception $e) {
    $members = [];
}

try {
    $pakets = $pdo->query("SELECT * FROM tb_paket ORDER BY id")->fetchAll();
} catch (Exception $e) {
    $pakets = [];
}

include 'include/header.php';
include 'include/sidebar.php';
?>

<?php if (isset($_GET['msg'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Berhasil!',text:'Transaksi berhasil diproses.',timer:2000,showConfirmButton:false}));</script>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error!',text:'<?= addslashes($errorMsg) ?>'}));</script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="transaksi.php" class="btn btn-sm <?= !$filterStatus ? 'btn-primary-custom' : 'btn-secondary' ?>" style="border-radius:8px">Semua</a>
        <a href="transaksi.php?status=baru" class="btn btn-sm <?= $filterStatus === 'baru' ? 'btn-primary-custom' : 'btn-secondary' ?>" style="border-radius:8px">Baru</a>
        <a href="transaksi.php?status=proses" class="btn btn-sm <?= $filterStatus === 'proses' ? 'btn-primary-custom' : 'btn-secondary' ?>" style="border-radius:8px">Proses</a>
        <a href="transaksi.php?status=selesai" class="btn btn-sm <?= $filterStatus === 'selesai' ? 'btn-primary-custom' : 'btn-secondary' ?>" style="border-radius:8px">Selesai</a>
        <a href="transaksi.php?status=diambil" class="btn btn-sm <?= $filterStatus === 'diambil' ? 'btn-primary-custom' : 'btn-secondary' ?>" style="border-radius:8px">Diambil</a>
    </div>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Transaksi Baru</button>
</div>

<div class="glass-card fade-in">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead><tr><th>Invoice</th><th>Member</th><th>Paket</th><th>Berat</th><th>Total</th><th>Uang Muka</th><th>Status</th><th>Bayar</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($data as $t): ?>
                <tr>
                    <td><code style="color:var(--cyan)"><?= htmlspecialchars($t['kode_invoice']) ?></code></td>
                    <td><?= htmlspecialchars($t['member_nama']) ?></td>
                    <td><?= htmlspecialchars($t['nama_paket']) ?></td>
                    <td><?= number_format($t['berat'], 1) ?> kg</td>
                    <td>Rp <?= number_format($t['biaya'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($t['uang_muka'], 0, ',', '.') ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <select name="status" class="form-select form-select-dark" style="width:auto;display:inline;padding:4px 8px;font-size:12px;border-radius:6px;" onchange="this.form.submit()">
                                <?php foreach (['baru','proses','selesai','diambil'] as $s): ?>
                                <option value="<?= $s ?>" <?= $t['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td><span class="badge-status <?= $t['pembayaran'] === 'dibayar' ? 'badge-dibayar' : 'badge-belum' ?>"><?= $t['pembayaran'] === 'dibayar' ? 'Lunas' : 'Belum' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($t['pembayaran'] === 'belum_dibayar'): ?>
                            <a href="konfirmasi_bayar.php?id=<?= $t['id'] ?>" class="btn btn-success-custom btn-sm" title="Bayar"><i class="fas fa-money-bill"></i></a>
                            <?php endif; ?>
                            <a href="cetak_invoice.php?id=<?= $t['id'] ?>" target="_blank" class="btn btn-primary-custom btn-sm" title="Cetak"><i class="fas fa-print"></i></a>
                            <button class="btn btn-danger-custom btn-sm" onclick="confirmDelete('transaksi.php?delete=<?= $t['id'] ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($data)): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada transaksi</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>Transaksi Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" id="formTransaksi"><input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="row">
            <!-- Member -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Member</label>
                <select name="id_member" id="selectMember" class="form-select form-select-dark" required>
                    <option value="">-- Pilih Member --</option>
                    <?php foreach ($members as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['kode_member'] . ' - ' . $m['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Outlet -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Outlet</label>
                <input type="text" class="form-control form-control-dark" value="<?= htmlspecialchars($_SESSION['outlet_nama']) ?>" readonly>
            </div>
            <!-- Paket -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Paket Layanan</label>
                <select name="id_paket" id="selectPaket" class="form-select form-select-dark" required>
                    <option value="" data-harga="0">-- Pilih Paket --</option>
                    <?php foreach ($pakets as $pk): ?>
                    <option value="<?= $pk['id'] ?>" data-harga="<?= $pk['harga_per_kg'] ?>">
                        <?= htmlspecialchars($pk['nama_paket']) ?> — Rp <?= number_format($pk['harga_per_kg'], 0, ',', '.') ?>/kg
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Berat -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Berat (kg)</label>
                <input type="number" name="berat" id="inputBerat" class="form-control form-control-dark" placeholder="0" min="0.1" step="0.1" required>
            </div>
            <!-- Tanggal -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Tanggal</label>
                <input type="date" name="tgl" class="form-control form-control-dark" value="<?= date('Y-m-d') ?>" required>
            </div>
            <!-- Batas Waktu -->
            <div class="col-md-6 mb-3">
                <label class="form-label-dark">Batas Waktu</label>
                <input type="date" name="batas_waktu" class="form-control form-control-dark" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
            </div>
        </div>

        <!-- Ringkasan Biaya -->
        <div style="background:var(--bg-input);border-radius:12px;padding:16px;margin-top:8px;">
            <h6 style="color:var(--text-secondary);font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;"><i class="fas fa-calculator me-2"></i>Ringkasan Biaya</h6>
            <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                <span style="color:var(--text-muted)">Subtotal</span>
                <span id="displaySubtotal" style="color:var(--text-primary)">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                <span style="color:var(--text-muted)">Pajak & Tip</span>
                <span style="color:var(--text-primary)">Rp 4.000</span>
            </div>
            <hr style="border-color:var(--border);margin:8px 0;">
            <div class="d-flex justify-content-between mb-3" style="font-size:16px;font-weight:700;">
                <span style="color:var(--text-primary)">Total</span>
                <span id="displayTotal" style="color:var(--green)">Rp 4.000</span>
            </div>

            <!-- Uang Muka -->
            <div class="mb-2">
                <label class="form-label-dark">Uang Muka (Rp)</label>
                <input type="number" name="uang_muka" id="inputUangMuka" class="form-control form-control-dark" placeholder="0" min="0" required>
            </div>
            <div class="d-flex justify-content-between" style="font-size:15px;font-weight:600;">
                <span style="color:var(--text-primary)">Kembalian</span>
                <span id="displayKembalian" style="color:var(--cyan)">Rp 0</span>
            </div>
        </div>

        <!-- Keterangan & Pembayaran -->
        <div class="row mt-3">
            <div class="col-md-8 mb-3">
                <label class="form-label-dark">Keterangan</label>
                <input type="text" name="keterangan" class="form-control form-control-dark" value="Menggunakan aplikasi MyLaundry">
            </div>
            <?php if (isAdmin() || isOwner()): ?>
            <div class="col-md-4 mb-3">
                <label class="form-label-dark">Status Bayar</label>
                <select name="pembayaran" class="form-select form-select-dark">
                    <option value="belum_dibayar">Belum Lunas</option>
                    <option value="dibayar">Lunas</option>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="pembayaran" value="belum_dibayar">
            <?php endif; ?>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Simpan & Lanjut</button></div>
    </form>
</div></div></div>

<?php include 'include/footer.php'; ?>

<script>
// Paket data for calculation
const paketData = {};
<?php foreach ($pakets as $pk): ?>
paketData[<?= $pk['id'] ?>] = <?= $pk['harga_per_kg'] ?>;
<?php endforeach; ?>

const PAJAK = 4000;

function formatRupiah(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function hitungBiaya() {
    const paketId = document.getElementById('selectPaket').value;
    const berat = parseFloat(document.getElementById('inputBerat').value) || 0;
    const uangMuka = parseInt(document.getElementById('inputUangMuka').value) || 0;

    const harga = paketData[paketId] || 0;
    const subtotal = Math.round(harga * berat);
    const total = subtotal + PAJAK;
    const kembalian = Math.max(0, uangMuka - total);

    document.getElementById('displaySubtotal').textContent = formatRupiah(subtotal);
    document.getElementById('displayTotal').textContent = formatRupiah(total);
    document.getElementById('displayKembalian').textContent = formatRupiah(kembalian);
}

document.getElementById('selectPaket').addEventListener('change', hitungBiaya);
document.getElementById('inputBerat').addEventListener('input', hitungBiaya);
document.getElementById('inputUangMuka').addEventListener('input', hitungBiaya);

// Select2 inside modal
$(document).ready(function() {
    var $modal = $('#addModal');
    $modal.on('shown.bs.modal', function() {
        $('#selectMember').select2({
            theme: 'default', width: '100%',
            dropdownParent: $modal,
            placeholder: '-- Pilih Member --', allowClear: true
        });
    });
});
</script>
