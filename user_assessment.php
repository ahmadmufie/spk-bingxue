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

ensureC1AssessmentColumns($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operatingEquipment = min(5, max(1, (int)($_POST['skill_operating_equipment'] ?? $_POST['skill_communication'] ?? 3)));
    $sop                 = min(5, max(1, (int)($_POST['skill_sop'] ?? $_POST['skill_cooperation'] ?? 3)));
    $speedAccuracy       = min(5, max(1, (int)($_POST['skill_speed_accuracy'] ?? $_POST['skill_ethics'] ?? 3)));
    $customerService     = min(5, max(1, (int)($_POST['skill_customer_service'] ?? $_POST['skill_technical'] ?? 3)));
    $teamwork            = min(5, max(1, (int)($_POST['skill_teamwork'] ?? 3)));

    $c1 = calcC1Score($db, $operatingEquipment, $sop, $speedAccuracy, $customerService, $teamwork);

    $stmt2 = $db->prepare("UPDATE applicants SET
        skill_communication=?, skill_cooperation=?, skill_ethics=?, skill_technical=?,
        skill_operating_equipment=?, skill_sop=?, skill_speed_accuracy=?,
        skill_customer_service=?, skill_teamwork=?, c1_score=?, self_assessment_filled=1 WHERE user_id=?");
    $stmt2->bind_param("ddddddddddi", $operatingEquipment, $sop, $speedAccuracy, $customerService, $operatingEquipment, $sop, $speedAccuracy, $customerService, $teamwork, $c1, $userId);
    $stmt2->execute();

    calculateSAW();

    flash('Self-assessment berhasil disimpan!', 'success');
    redirect('user_dashboard.php');
}

renderHeader('Self-Assessment', 'user');
renderNav('user', 'user_assessment.php');
?>

<div class="page-header">
    <div class="breadcrumb">Dashboard → Self-Assessment</div>
    <h1>Self-Assessment Keterampilan (C1)</h1>
    <p>Nilai kemampuan diri Anda secara jujur pada skala 1-5 untuk setiap aspek berikut.</p>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-title">Penilaian Keterampilan (Bobot C1: 30%)</div>
    <form method="POST" id="assessmentForm">
        <?php
        $skills = [
            ['key' => 'skill_operating_equipment', 'label' => 'Kemampuan mengoperasikan peralatan kerja', 'sub' => 'Skor 1-5', 'icon' => '🛠️', 'val' => normalizeC1SliderValue($app['skill_operating_equipment'] ?? $app['skill_communication'] ?? 3)],
            ['key' => 'skill_sop',                 'label' => 'Kemampuan membuat produk sesuai SOP', 'sub' => 'Skor 1-5', 'icon' => '📋', 'val' => normalizeC1SliderValue($app['skill_sop'] ?? $app['skill_cooperation'] ?? 3)],
            ['key' => 'skill_speed_accuracy',      'label' => 'Kecepatan dan ketelitian bekerja', 'sub' => 'Skor 1-5', 'icon' => '⚡', 'val' => normalizeC1SliderValue($app['skill_speed_accuracy'] ?? $app['skill_ethics'] ?? 3)],
            ['key' => 'skill_customer_service',    'label' => 'Kemampuan melayani pelanggan', 'sub' => 'Skor 1-5', 'icon' => '🤝', 'val' => normalizeC1SliderValue($app['skill_customer_service'] ?? $app['skill_technical'] ?? 3)],
            ['key' => 'skill_teamwork',            'label' => 'Kemampuan bekerjasama tim', 'sub' => 'Skor 1-5', 'icon' => '👥', 'val' => normalizeC1SliderValue($app['skill_teamwork'] ?? 3)],
        ];
        foreach ($skills as $s):
        ?>
        <div class="form-group">
            <label class="form-label"><?= $s['icon'] ?> <?= $s['label'] ?></label>
            <p class="text-sm text-muted mb-4"><?= $s['sub'] ?></p>
            <div class="range-wrap">
                <input type="range" class="form-range" name="<?= $s['key'] ?>"
                       id="<?= $s['key'] ?>" min="1" max="5" step="1"
                       value="<?= (int)$s['val'] ?>"
                       oninput="document.getElementById('val_<?= $s['key'] ?>').textContent=this.value">
                <span class="range-val" id="val_<?= $s['key'] ?>"><?= (int)$s['val'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <p class="text-sm text-muted">Skor akan otomatis dihitung berdasarkan rumus SAW.</p>

        <div class="separator"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan Self-Assessment</button>
            <a href="user_dashboard.php" class="btn btn-outline">Kembali</a>
        </div>
    </form>
</div>

<?php renderFooter(); ?>
