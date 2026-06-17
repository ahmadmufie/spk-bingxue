<?php
session_start();
require_once 'config.php';
require_once 'partials/header.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_weights'])) {
        foreach ($_POST['weight'] as $cid => $w) {
            $cid = (int)$cid;
            $w   = (float)$w;
            if ($w < 1) $w = 1;
            if ($w > 100) $w = 100;
            $stmt = $db->prepare("UPDATE criteria SET weight=? WHERE id=?");
            $stmt->bind_param("di", $w, $cid);
            $stmt->execute();
        }
        flash('Bobot kriteria berhasil diperbarui.', 'success');
        redirect('admin_criteria.php');
    }

    if (isset($_POST['update_subcriteria'])) {
        foreach ($_POST['subval'] as $sid => $v) {
            $sid = (int)$sid;
            $v   = (float)$v;
            if ($v < 0) $v = 0;
            $stmt = $db->prepare("UPDATE sub_criteria SET value=? WHERE id=?");
            $stmt->bind_param("di", $v, $sid);
            $stmt->execute();
        }
        flash('Nilai sub-kriteria berhasil diperbarui.', 'success');
        redirect('admin_criteria.php');
    }
}

$critRes = $db->query("SELECT * FROM criteria ORDER BY code");
$criteriaList = [];
while ($c = $critRes->fetch_assoc()) {
    $subRes = $db->query("SELECT * FROM sub_criteria WHERE criteria_id={$c['id']} ORDER BY id");
    $subs = [];
    while ($s = $subRes->fetch_assoc()) $subs[] = $s;
    $c['subs'] = $subs;
    $criteriaList[] = $c;
}

renderHeader('Kelola Kriteria', 'admin');
renderNav('admin', 'admin_criteria.php');
?>

<div class="page-header">
    <div class="breadcrumb">Admin → Kelola Kriteria</div>
    <h1>Manajemen Kriteria & Bobot</h1>
    <p>Edit bobot kriteria dan nilai sub-kriteria. Perubahan langsung diterapkan pada perhitungan SAW.</p>
</div>

<!-- Criteria Weights -->
<div class="card mb-6">
    <div class="card-title">Bobot Kriteria (W)</div>
    <form method="POST">
        <input type="hidden" name="update_weights" value="1">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Bobot Saat Ini (%)</th><th>Edit Bobot</th></tr>
                </thead>
                <tbody>
                <?php foreach ($criteriaList as $c): ?>
                <tr>
                    <td><span class="criteria-code"><?= htmlspecialchars($c['code']) ?></span></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($c['type']) ?></span></td>
                    <td><strong><?= number_format($c['weight'], 1) ?>%</strong></td>
                    <td>
                        <input type="number" name="weight[<?= $c['id'] ?>]"
                               value="<?= number_format($c['weight'], 0) ?>"
                               min="1" max="100" step="1" class="sub-val-input" style="width:90px">
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Bobot Kriteria</button>
            <span class="text-sm text-muted" style="margin-left:12px;">Total idealnya = 100. Sistem akan normalisasi otomatis.</span>
        </div>
    </form>
</div>

<!-- Sub-criteria Values -->
<div class="card">
    <div class="card-title">Nilai Sub-Kriteria</div>
    <form method="POST">
        <input type="hidden" name="update_subcriteria" value="1">
        <div class="criteria-grid">
        <?php foreach ($criteriaList as $c): ?>
        <div class="criteria-card">
            <div class="criteria-head">
                <div class="flex gap-2" style="align-items:center">
                    <span class="criteria-code"><?= htmlspecialchars($c['code']) ?></span>
                    <span style="font-weight:600"><?= htmlspecialchars($c['name']) ?></span>
                </div>
                <span class="text-sm text-muted">W: <?= number_format($c['weight'], 0) ?>%</span>
            </div>
            <div class="criteria-body">
                <?php if (empty($c['subs'])): ?>
                    <p class="text-muted text-sm">Tidak ada sub-kriteria.</p>
                <?php else: ?>
                    <?php foreach ($c['subs'] as $s): ?>
                    <div class="sub-row">
                        <span class="sub-label"><?= htmlspecialchars($s['label']) ?></span>
                        <input type="number" name="subval[<?= $s['id'] ?>]"
                               value="<?= number_format($s['value'], 0) ?>"
                               min="0" max="100" step="5" class="sub-val-input">
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <div class="mt-6">
            <button type="submit" class="btn btn-primary">Simpan Nilai Sub-Kriteria</button>
        </div>
    </form>
</div>

<?php renderFooter(); ?>
