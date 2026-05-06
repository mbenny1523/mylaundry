<?php
$pageTitle = 'Data Outlet';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO tb_outlet (nama, alamat, telp) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['nama'], $_POST['alamat'], $_POST['telp']]);
        header('Location: outlet.php?msg=added'); exit;
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE tb_outlet SET nama=?, alamat=?, telp=? WHERE id=?");
        $stmt->execute([$_POST['nama'], $_POST['alamat'], $_POST['telp'], $_POST['id']]);
        header('Location: outlet.php?msg=updated'); exit;
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tb_outlet WHERE id=?")->execute([$_GET['delete']]);
    header('Location: outlet.php?msg=deleted'); exit;
}

$data = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama")->fetchAll();

include 'include/header.php';
include 'include/sidebar.php';
?>

<?php if (isset($_GET['msg'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Berhasil!',text:'Data outlet berhasil diproses.',timer:2000,showConfirmButton:false}));</script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-muted);margin:0;">Total: <?= count($data) ?> outlet</p>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i>Tambah Outlet
    </button>
</div>

<div class="glass-card fade-in">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead><tr><th>No</th><th>Nama Outlet</th><th>Alamat</th><th>Telepon</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($data as $i => $o): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($o['nama']) ?></strong></td>
                    <td><?= htmlspecialchars($o['alamat']) ?></td>
                    <td><?= htmlspecialchars($o['telp']) ?></td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-warning-custom btn-sm" onclick='openEdit(<?= json_encode($o) ?>)'><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger-custom btn-sm" onclick="confirmDelete('outlet.php?delete=<?= $o['id'] ?>')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($data)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-store me-2"></i>Tambah Outlet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Nama Outlet</label><input type="text" name="nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Alamat</label><textarea name="alamat" class="form-control form-control-dark" rows="2" required></textarea></div>
        <div class="mb-3"><label class="form-label-dark">Telepon</label><input type="tel" name="telp" class="form-control form-control-dark" pattern="[0-9]+" inputmode="numeric" title="Hanya angka" required oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Simpan</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Outlet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Nama Outlet</label><input type="text" name="nama" id="edit_nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Alamat</label><textarea name="alamat" id="edit_alamat" class="form-control form-control-dark" rows="2" required></textarea></div>
        <div class="mb-3"><label class="form-label-dark">Telepon</label><input type="tel" name="telp" id="edit_telp" class="form-control form-control-dark" pattern="[0-9]+" inputmode="numeric" title="Hanya angka" required oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Update</button></div>
    </form>
</div></div></div>

<script>
function openEdit(d) {
    document.getElementById('edit_id').value = d.id;
    document.getElementById('edit_nama').value = d.nama;
    document.getElementById('edit_alamat').value = d.alamat;
    document.getElementById('edit_telp').value = d.telp;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include 'include/footer.php'; ?>
