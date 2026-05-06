<?php
$pageTitle = 'Data Pengguna';
require_once 'auth_check.php';
if (!isAdmin() && !isOwner()) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO tb_user (nama, username, password, id_outlet, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['nama'], $_POST['username'], $hash, $_POST['id_outlet'], $_POST['role']]);
        header('Location: pengguna.php?msg=added'); exit;
    } elseif ($action === 'edit') {
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE tb_user SET nama=?, username=?, password=?, id_outlet=?, role=? WHERE id=?");
            $stmt->execute([$_POST['nama'], $_POST['username'], $hash, $_POST['id_outlet'], $_POST['role'], $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE tb_user SET nama=?, username=?, id_outlet=?, role=? WHERE id=?");
            $stmt->execute([$_POST['nama'], $_POST['username'], $_POST['id_outlet'], $_POST['role'], $_POST['id']]);
        }
        header('Location: pengguna.php?msg=updated'); exit;
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM tb_user WHERE id=?")->execute([$_GET['delete']]);
    header('Location: pengguna.php?msg=deleted'); exit;
}

$data = $pdo->query("SELECT u.*, o.nama as outlet_nama FROM tb_user u JOIN tb_outlet o ON u.id_outlet = o.id ORDER BY u.nama")->fetchAll();
$outlets = $pdo->query("SELECT * FROM tb_outlet ORDER BY nama")->fetchAll();

include 'include/header.php';
include 'include/sidebar.php';
?>

<?php if (isset($_GET['msg'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Berhasil!',text:'Data pengguna berhasil diproses.',timer:2000,showConfirmButton:false}));</script>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-muted);margin:0;">Total: <?= count($data) ?> pengguna</p>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Tambah Pengguna</button>
</div>

<div class="glass-card fade-in">
    <div class="table-responsive">
        <table class="table-dark-custom">
            <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Outlet</th><th>Role</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($data as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($u['nama']) ?></td>
                    <td><code style="color:var(--cyan)"><?= htmlspecialchars($u['username']) ?></code></td>
                    <td><?= htmlspecialchars($u['outlet_nama']) ?></td>
                    <td><span class="badge-status <?= $u['role'] === 'admin' ? 'badge-dibayar' : ($u['role'] === 'owner' ? 'badge-diambil' : 'badge-baru') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td class="d-flex gap-1">
                        <button class="btn btn-warning-custom btn-sm" onclick='openEdit(<?= json_encode($u) ?>)'><i class="fas fa-edit"></i></button>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <button class="btn btn-danger-custom btn-sm" onclick="confirmDelete('pengguna.php?delete=<?= $u['id'] ?>')"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="add">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Nama</label><input type="text" name="nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Username</label><input type="text" name="username" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Password</label><input type="password" name="password" class="form-control form-control-dark" required minlength="6"></div>
        <div class="mb-3"><label class="form-label-dark">Outlet</label><select name="id_outlet" class="form-select form-select-dark" required><?php foreach ($outlets as $o): ?><option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label-dark">Role</label><select name="role" class="form-select form-select-dark" required><option value="kasir">Kasir</option><option value="admin">Admin</option><option value="owner">Owner</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Simpan</button></div>
    </form>
</div></div></div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label-dark">Nama</label><input type="text" name="nama" id="edit_nama" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Username</label><input type="text" name="username" id="edit_username" class="form-control form-control-dark" required></div>
        <div class="mb-3"><label class="form-label-dark">Password <small style="color:var(--text-muted)">(kosongkan jika tidak diubah)</small></label><input type="password" name="password" class="form-control form-control-dark" minlength="6"></div>
        <div class="mb-3"><label class="form-label-dark">Outlet</label><select name="id_outlet" id="edit_outlet" class="form-select form-select-dark" required><?php foreach ($outlets as $o): ?><option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label-dark">Role</label><select name="role" id="edit_role" class="form-select form-select-dark" required><option value="kasir">Kasir</option><option value="admin">Admin</option><option value="owner">Owner</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary-custom">Update</button></div>
    </form>
</div></div></div>

<script>
function openEdit(d) {
    document.getElementById('edit_id').value = d.id;
    document.getElementById('edit_nama').value = d.nama;
    document.getElementById('edit_username').value = d.username;
    document.getElementById('edit_outlet').value = d.id_outlet;
    document.getElementById('edit_role').value = d.role;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include 'include/footer.php'; ?>
