<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: transaksi.php'); exit; }

$trx = $pdo->prepare("SELECT t.*, m.nama as member_nama, m.kode_member, m.telp as member_telp, 
    o.nama as outlet_nama, o.alamat as outlet_alamat, o.telp as outlet_telp, 
    u.nama as user_nama, p.nama_paket, p.harga_per_kg
    FROM tb_transaksi t 
    JOIN tb_member m ON t.id_member = m.id 
    JOIN tb_outlet o ON t.id_outlet = o.id 
    JOIN tb_user u ON t.id_user = u.id 
    JOIN tb_paket p ON t.id_paket = p.id
    WHERE t.id = ?");
$trx->execute([$id]);
$trx = $trx->fetch();
if (!$trx) { header('Location: transaksi.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($trx['kode_invoice']) ?> - MyLaundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .invoice-container { max-width: 500px; width: 100%; }
        .invoice-card { background: #1e293b; border-radius: 20px; padding: 36px 32px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        .invoice-header { text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed rgba(255,255,255,0.1); }
        .invoice-header .logo { font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 700; color: #3b82f6; margin-bottom: 4px; }
        .invoice-header .outlet { font-size: 13px; color: #94a3b8; }
        .invoice-header .outlet-detail { font-size: 12px; color: #64748b; }
        .invoice-code { text-align: center; margin-bottom: 20px; }
        .invoice-code span { background: rgba(59,130,246,0.1); color: #60a5fa; padding: 6px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; }
        .detail-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; }
        .detail-row .label { color: #64748b; }
        .detail-row .value { color: #e2e8f0; font-weight: 500; text-align: right; }
        .divider { border-top: 1px dashed rgba(255,255,255,0.1); margin: 14px 0; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 0; }
        .total-row .label { font-size: 16px; font-weight: 600; }
        .total-row .value { font-size: 20px; font-weight: 700; color: #10b981; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .status-paid { background: rgba(16,185,129,0.15); color: #34d399; }
        .status-unpaid { background: rgba(239,68,68,0.15); color: #f87171; }
        .invoice-footer { text-align: center; margin-top: 20px; padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.1); font-size: 12px; color: #64748b; }
        .btn-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .btn-print { padding: 10px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-blue:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.35); }
        .btn-gray { background: #334155; color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-gray:hover { background: #475569; color: #fff; }
        .ket-box { background: rgba(59,130,246,0.08); border-radius: 8px; padding: 10px 14px; margin-top: 12px; font-size: 12px; color: #94a3b8; text-align: center; }

        @media print {
            body { background: #fff !important; color: #000 !important; }
            .invoice-card { background: #fff !important; box-shadow: none !important; border: none !important; }
            .invoice-header .logo { color: #000 !important; }
            .invoice-header .outlet, .invoice-header .outlet-detail { color: #555 !important; }
            .invoice-code span { background: #eee !important; color: #333 !important; }
            .detail-row .label { color: #666 !important; }
            .detail-row .value { color: #000 !important; }
            .total-row .value { color: #000 !important; }
            .invoice-footer { color: #999 !important; }
            .divider { border-color: #ccc !important; }
            .ket-box { background: #f5f5f5 !important; color: #555 !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="logo">🧺 MyLaundry</div>
                <div class="outlet"><?= htmlspecialchars($trx['outlet_nama']) ?></div>
                <div class="outlet-detail"><?= htmlspecialchars($trx['outlet_alamat']) ?> | <?= htmlspecialchars($trx['outlet_telp']) ?></div>
            </div>

            <div class="invoice-code"><span><?= htmlspecialchars($trx['kode_invoice']) ?></span></div>

            <div class="detail-row"><span class="label">Pelanggan</span><span class="value"><?= htmlspecialchars($trx['member_nama']) ?></span></div>
            <div class="detail-row"><span class="label">Kode Member</span><span class="value"><?= htmlspecialchars($trx['kode_member']) ?></span></div>
            <div class="detail-row"><span class="label">Telepon</span><span class="value"><?= htmlspecialchars($trx['member_telp']) ?></span></div>

            <div class="divider"></div>

            <div class="detail-row"><span class="label">Paket</span><span class="value"><?= htmlspecialchars($trx['nama_paket']) ?></span></div>
            <div class="detail-row"><span class="label">Harga/kg</span><span class="value">Rp <?= number_format($trx['harga_per_kg'], 0, ',', '.') ?></span></div>
            <div class="detail-row"><span class="label">Berat</span><span class="value"><?= number_format($trx['berat'], 1) ?> kg</span></div>
            <div class="detail-row"><span class="label">Subtotal</span><span class="value">Rp <?= number_format($trx['subtotal'], 0, ',', '.') ?></span></div>
            <div class="detail-row"><span class="label">Pajak & Tip</span><span class="value">Rp <?= number_format($trx['pajak'], 0, ',', '.') ?></span></div>

            <div class="divider"></div>

            <div class="total-row"><span class="label">Total</span><span class="value">Rp <?= number_format($trx['biaya'], 0, ',', '.') ?></span></div>
            <div class="detail-row"><span class="label">Uang Muka</span><span class="value" style="font-weight:600">Rp <?= number_format($trx['uang_muka'], 0, ',', '.') ?></span></div>
            <div class="detail-row"><span class="label">Kembalian</span><span class="value" style="color:#06b6d4;font-weight:700">Rp <?= number_format($trx['kembalian'], 0, ',', '.') ?></span></div>

            <div class="divider"></div>

            <div class="detail-row"><span class="label">Tanggal Masuk</span><span class="value"><?= date('d/m/Y', strtotime($trx['tgl'])) ?></span></div>
            <div class="detail-row"><span class="label">Batas Waktu</span><span class="value"><?= date('d/m/Y', strtotime($trx['batas_waktu'])) ?></span></div>
            <div class="detail-row"><span class="label">Status</span><span class="value"><?= ucfirst($trx['status']) ?></span></div>
            <div class="detail-row">
                <span class="label">Pembayaran</span>
                <span class="value"><span class="status-badge <?= $trx['pembayaran'] === 'dibayar' ? 'status-paid' : 'status-unpaid' ?>"><?= $trx['pembayaran'] === 'dibayar' ? 'Lunas' : 'Belum Lunas' ?></span></span>
            </div>
            <?php if ($trx['tgl_bayar']): ?>
            <div class="detail-row"><span class="label">Tgl Bayar</span><span class="value"><?= date('d/m/Y', strtotime($trx['tgl_bayar'])) ?></span></div>
            <?php endif; ?>
            <div class="detail-row"><span class="label">Kasir</span><span class="value"><?= htmlspecialchars($trx['user_nama']) ?></span></div>

            <?php if (!empty($trx['keterangan'])): ?>
            <div class="ket-box"><i class="fas fa-info-circle" style="margin-right:4px"></i><?= htmlspecialchars($trx['keterangan']) ?></div>
            <?php endif; ?>

            <div class="invoice-footer">
                Terima kasih telah menggunakan layanan kami!<br>
                <small>Dicetak: <?= date('d/m/Y H:i') ?></small>
            </div>
        </div>

        <div class="btn-actions no-print">
            <a href="transaksi.php" class="btn-print btn-gray">← Kembali</a>
            <button onclick="window.print()" class="btn-print btn-blue">🖨 Cetak</button>
        </div>
    </div>
</body>
</html>
