<?php
session_start();
require_once 'config.php';
require_once 'saw_calculation.php';
require_once 'partials/header.php';
requireUser();

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM applicants WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$app = $res->fetch_assoc();

if (!$app || !$app['personal_data_filled']) {
    flash('Harap isi data diri terlebih dahulu.', 'error');
    redirect('user_personal.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comm   = min(100, max(0, (int)($_POST['skill_communication'] ?? 0)));
    $coop   = min(100, max(0, (int)($_POST['skill_cooperation'] ?? 0)));
    $ethics = min(100, max(0, (int)($_POST['skill_ethics'] ?? 0)));
    $tech   = min(100, max(0, (int)($_POST['skill_technical'] ?? 0)));

    $c1 = calcC1Score($db, $comm, $coop, $ethics, $tech);

    $stmt2 = $db->prepare("UPDATE applicants SET
        skill_communication=?, skill_cooperation=?, skill_ethics=?, skill_technical=?,
        c1_score=?, self_assessment_filled=1 WHERE user_id=?");
    $stmt2->bind_param("dddddi", $comm, $coop, $ethics, $tech, $c1, $userId);
    $stmt2->execute();

    flash('Self-assessment berhasil disimpan!', 'success');
    redirect('user_dashboard.php');
}

renderHeader('Self-Assessment', 'user');
renderNav('user', 'user_assessment.php');
?>

<div class="page-header">
    <div class="breadcrumb">Dashboard → Self-Assessment</div>
    <h1>Self-Assessment Keterampilan (C1)</h1>
    <p>Nilai kemampuan diri Anda secara jujur pada rentang 0-100.</p>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-title">Penilaian Keterampilan (Bobot C1: 30%)</div>
    <form method="POST" id="assessmentForm">
        <?php
        $skills = [
            ['key' => 'skill_communication', 'label' => 'Komunikasi', 'sub' => 'Bobot sub-kriteria: 40% dari C1', 'icon' => '💬', 'val' => $app['skill_communication'] ?? 70],
            ['key' => 'skill_cooperation',   'label' => 'Kerjasama',  'sub' => 'Bobot sub-kriteria: 30% dari C1', 'icon' => '🤝', 'val' => $app['skill_cooperation'] ?? 70],
            ['key' => 'skill_ethics',        'label' => 'Etika',      'sub' => 'Bobot sub-kriteria: 20% dari C1', 'icon' => '⚖️', 'val' => $app['skill_ethics'] ?? 70],
            ['key' => 'skill_technical',     'label' => 'Teknis',     'sub' => 'Bobot sub-kriteria: 10% dari C1', 'icon' => '🔧', 'val' => $app['skill_technical'] ?? 70],
        ];
        foreach ($skills as $s):
        ?>
        <div class="form-group">
            <label class="form-label"><?= $s['icon'] ?> <?= $s['label'] ?></label>
            <p class="text-sm text-muted mb-4"><?= $s['sub'] ?></p>
            <div class="range-wrap">
                <input type="range" class="form-range" name="<?= $s['key'] ?>"
                       id="<?= $s['key'] ?>" min="0" max="100" step="5"
                       value="<?= (int)$s['val'] ?>"
                       oninput="document.getElementById('val_<?= $s['key'] ?>').textContent=this.value">
                <span class="range-val" id="val_<?= $s['key'] ?>"><?= (int)$s['val'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="separator"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan Self-Assessment</button>
            <a href="user_dashboard.php" class="btn btn-outline">Kembali</a>
        </div>
    </form>
</div>

<?php renderFooter(); ?>
