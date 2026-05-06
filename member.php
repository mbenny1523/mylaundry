<?php
$pageTitle = 'Data Member';
require_once 'auth_check.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO tb_member (kode_member, nama, alamat, jenis_kelamin, telp) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['kode_member'], $_POST['nama'], $_POST['alamat'], $_POST['jenis_kelamin'], $_POST['telp']]);
        header('Location: member.php?msg=added'); exit;
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE tb_member SET kode_member=?, nama=?, alamat=?, jenis_kelamin=?, telp=? WHERE id=?");
        $stmt->execute([$_POST['kode_member'], $_POST['nama'], $_POST['alamat'], $_POST['jenis_kelamin'], $_POST['telp'], $_POST['id']]);
        header('Location: member.php?msg=updated'); exit;
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tb_member WHERE id=?")->execute([$_GET['delete']]);
    header('Location: member.php?msg=deleted'); exit;
}

$data = $pdo->query("SELECT * FROM tb_member ORDER BY nama")->fetchAll();

include 'include/header.php';
include 'include/sidebar.php';
?>

<?php if (isset($_GET['msg'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Berhasil!',text:'<?= $_GET['msg'] === 'added' ? 'Data member ditambahkan.' : ($_GET['msg'] === 'updated' ? 'Data member diperbarui.' : 'Data member dihapus.') ?>',timer:2000,showConfirmButton:false}));</script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-muted);margin:0;">Total: <?= count($data) ?> member</p>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-2"></i>Tambah Member
    </button>
</div>

<div class="glass-card fade-in">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead>
                <tr><th>No</th><th>Kode Member</th><th>Nama</th><th>Alamat</th><th>JK</th><th>Telp</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($data as $i => $m): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code style="color:var(--cyan)"><?= htmlspecialchars($m['kode_member']) ?></code></td>
                    <td><?= htmlspecialchars($m['nama']) ?></td>
                    <td><?= htmlspecialchars($m['alamat']) ?></td>
                    <td><?= $m['jenis_kelamin'] ?></td>
                    <td><?= htmlspecialchars($m['telp']) ?></td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-warning-custom btn-sm" onclick='openEdit(<?= json_encode($m) ?>)'><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger-custom btn-sm" onclick="confirmDelete('member.php?delete=<?= $m['id'] ?>')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($data)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px;">Belum ada data</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Kode Member</label><input type="text" name="kode_member" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Nama</label><input type="text" name="nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Alamat</label><textarea name="alamat" class="form-control form-control-dark" rows="2" required></textarea></div>
        <div class="mb-3"><label class="form-label-dark">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select form-select-dark" required><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
        <div class="mb-3"><label class="form-label-dark">Telepon</label><input type="tel" name="telp" class="form-control form-control-dark" pattern="[0-9]+" inputmode="numeric" title="Hanya angka" required oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Simpan</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Kode Member</label><input type="text" name="kode_member" id="edit_kode_member" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Nama</label><input type="text" name="nama" id="edit_nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Alamat</label><textarea name="alamat" id="edit_alamat" class="form-control form-control-dark" rows="2" required></textarea></div>
        <div class="mb-3"><label class="form-label-dark">Jenis Kelamin</label><select name="jenis_kelamin" id="edit_jk" class="form-select form-select-dark" required><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
        <div class="mb-3"><label class="form-label-dark">Telepon</label><input type="tel" name="telp" id="edit_telp" class="form-control form-control-dark" pattern="[0-9]+" inputmode="numeric" title="Hanya angka" required oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Update</button></div>
    </form>
</div></div></div>

<script>
function openEdit(d) {
    document.getElementById('edit_id').value = d.id;
    document.getElementById('edit_kode_member').value = d.kode_member;
    document.getElementById('edit_nama').value = d.nama;
    document.getElementById('edit_alamat').value = d.alamat;
    document.getElementById('edit_jk').value = d.jenis_kelamin;
    document.getElementById('edit_telp').value = d.telp;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include 'include/footer.php'; ?>
