<?php
session_start();
require_once 'config.php';
require_once 'partials/header.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($db, $_POST['action'] ?? '');

    if ($action === 'resign') {
        $eid   = (int)$_POST['emp_id'];
        $notes = sanitize($db, $_POST['notes'] ?? 'Mengundurkan diri');
        $stmt  = $db->prepare("UPDATE employees SET status='resigned', end_date=CURDATE(), notes=? WHERE id=?");
        $stmt->bind_param("si", $notes, $eid);
        $stmt->execute();
        flash('Status karyawan diperbarui menjadi Keluar.', 'success');
        redirect('admin_employees.php');
    }

    if ($action === 'add_note') {
        $eid   = (int)$_POST['emp_id'];
        $pos   = sanitize($db, $_POST['position'] ?? '');
        $notes = sanitize($db, $_POST['notes'] ?? '');
        $stmt  = $db->prepare("UPDATE employees SET position=?, notes=? WHERE id=?");
        $stmt->bind_param("ssi", $pos, $notes, $eid);
        $stmt->execute();
        flash('Data karyawan berhasil diperbarui.', 'success');
        redirect('admin_employees.php');
    }
}

$tab = sanitize($db, $_GET['tab'] ?? 'active');

$active = $db->query("SELECT e.*, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY e.join_date DESC");
$resigned = $db->query("SELECT e.*, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status IN('resigned','terminated') ORDER BY e.end_date DESC");

renderHeader('Data Karyawan', 'admin');
renderNav('admin', 'admin_employees.php');
?>

<div class="page-header">
    <div class="breadcrumb">Admin → Karyawan</div>
    <h1>Manajemen Data Karyawan</h1>
    <p>Pantau karyawan aktif (Karyawan Masuk) dan riwayat karyawan keluar.</p>
</div>

<div class="stats-grid">
    <div class="stat-card green">
        <div class="stat-label">Karyawan Aktif</div>
        <div class="stat-value"><?= $active->num_rows ?></div>
        <div class="stat-sub">Masih bekerja</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Karyawan Keluar</div>
        <div class="stat-value"><?= $resigned->num_rows ?></div>
        <div class="stat-sub">Resign / Terminated</div>
    </div>
</div>

<!-- Tabs -->
<div class="flex gap-3 mb-6">
    <a href="admin_employees.php?tab=active" class="btn <?= $tab === 'active' ? 'btn-primary' : 'btn-outline' ?>">👥 Karyawan Masuk (<?= $active->num_rows ?>)</a>
    <a href="admin_employees.php?tab=resigned" class="btn <?= $tab === 'resigned' ? 'btn-primary' : 'btn-outline' ?>">🚪 Karyawan Keluar (<?= $resigned->num_rows ?>)</a>
</div>

<?php if ($tab === 'active'): ?>
<!-- Active Employees -->
<div class="card">
    <div class="card-title">Karyawan Aktif</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Jabatan</th><th>Tanggal Masuk</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php
            $active->data_seek(0);
            if ($active->num_rows === 0) {
                echo '<tr><td colspan="6" class="text-center text-muted" style="padding:30px">Belum ada karyawan aktif.</td></tr>';
            }
            while ($e = $active->fetch_assoc()):
            ?>
            <tr>
                <td><div style="font-weight:600"><?= htmlspecialchars($e['name']) ?></div></td>
                <td class="text-sm text-muted"><?= htmlspecialchars($e['email']) ?></td>
                <td>
                    <form method="POST" style="display:inline-flex;gap:6px;align-items:center">
                        <input type="hidden" name="action" value="add_note">
                        <input type="hidden" name="emp_id" value="<?= $e['id'] ?>">
                        <input type="text" name="position" value="<?= htmlspecialchars($e['position'] ?? 'Staff') ?>"
                               class="form-control" style="width:130px;padding:6px 10px;font-size:0.82rem">
                        <button type="submit" class="btn btn-outline btn-sm">Simpan</button>
                    </form>
                </td>
                <td><?= $e['join_date'] ?></td>
                <td><span class="badge badge-active">Aktif</span></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Tandai karyawan ini sebagai keluar?')">
                        <input type="hidden" name="action" value="resign">
                        <input type="hidden" name="emp_id" value="<?= $e['id'] ?>">
                        <input type="hidden" name="notes" value="Mengundurkan diri">
                        <button type="submit" class="btn btn-danger btn-sm">Tandai Keluar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- Resigned/Terminated Employees -->
<div class="card">
    <div class="card-title">Karyawan Keluar</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Jabatan</th><th>Tanggal Masuk</th><th>Tanggal Keluar</th><th>Status</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
            <?php
            $resigned->data_seek(0);
            if ($resigned->num_rows === 0) {
                echo '<tr><td colspan="7" class="text-center text-muted" style="padding:30px">Belum ada data karyawan keluar.</td></tr>';
            }
            while ($e = $resigned->fetch_assoc()):
            ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($e['name']) ?></td>
                <td class="text-sm text-muted"><?= htmlspecialchars($e['email']) ?></td>
                <td><?= htmlspecialchars($e['position'] ?? 'Staff') ?></td>
                <td><?= $e['join_date'] ?></td>
                <td><?= $e['end_date'] ?? '-' ?></td>
                <td><span class="badge badge-resigned"><?= ucfirst($e['status']) ?></span></td>
                <td class="text-sm"><?= htmlspecialchars($e['notes'] ?? '-') ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
