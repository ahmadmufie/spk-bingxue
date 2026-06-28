<?php
session_start();
require_once 'config.php';
require_once 'saw_calculation.php';
require_once 'partials/header.php';
requireUser();

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Ensure applicant record exists
$stmt = $db->prepare("SELECT * FROM applicants WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$app = $res->fetch_assoc();
if (!$app) {
    $ins = $db->prepare("INSERT INTO applicants (user_id) VALUES (?)");
    $ins->bind_param("i", $userId);
    $ins->execute();
    $stmt->execute();
    $res = $stmt->get_result();
    $app = $res->fetch_assoc();
}

// Valid options
$educations  = ['D3/S1/S2', 'SMA/SMK', 'SMP'];
$experiences = ['>3 Tahun', '3 Tahun', '2 Tahun', '1 Tahun', 'Tidak Ada Pengalaman'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $education  = sanitize($db, $_POST['education'] ?? '');
    $experience = sanitize($db, $_POST['experience'] ?? '');
    $age        = (int)($_POST['age'] ?? 0);

    $errors = [];
    if (!in_array($education, $educations))   $errors[] = 'Pilih pendidikan yang valid.';
    if (!in_array($experience, $experiences)) $errors[] = 'Pilih pengalaman yang valid.';
    if ($age < 18 || $age > 35)              $errors[] = 'Umur harus antara 18-35 tahun.';

    if (empty($errors)) {
        $c2 = calcC2Score($db, $experience);
        $c4 = calcC4Score($db, $education);
        $c5 = calcC5Score($db, $age);

        $stmt2 = $db->prepare("UPDATE applicants SET education=?, experience_years=?, age=?,
            c2_score=?, c4_score=?, c5_score=?, personal_data_filled=1 WHERE user_id=?");
        $stmt2->bind_param("ssiiddi", $education, $experience, $age, $c2, $c4, $c5, $userId);
        $stmt2->execute();

        calculateSAW();

        flash('Data diri berhasil disimpan!', 'success');
        redirect('user_dashboard.php');
    } else {
        flash(implode(' ', $errors), 'error');
    }
}

renderHeader('Data Diri', 'user');
renderNav('user', 'user_personal.php');
?>

<div class="page-header">
    <div class="breadcrumb">Dashboard → Data Diri</div>
    <h1>Formulir Data Diri</h1>
    <p>Isi data pendidikan, pengalaman kerja, dan usia Anda.</p>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-title">Data Pelamar (Kriteria C2, C4, C5)</div>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Pendidikan Terakhir (C4)</label>
            <select name="education" class="form-control" required>
                <option value="">-- Pilih Pendidikan --</option>
                <?php foreach ($educations as $edu): ?>
                    <option value="<?= $edu ?>" <?= ($app['education'] ?? '') === $edu ? 'selected' : '' ?>><?= htmlspecialchars($edu) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Pengalaman Kerja (C2)</label>
            <select name="experience" class="form-control" required>
                <option value="">-- Pilih Pengalaman --</option>
                <?php foreach ($experiences as $exp): ?>
                    <option value="<?= $exp ?>" <?= ($app['experience_years'] ?? '') === $exp ? 'selected' : '' ?>><?= htmlspecialchars($exp) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Umur (C5)</label>
            <input type="number" name="age" class="form-control" min="18" max="35"
                   value="<?= htmlspecialchars($app['age'] ?? '') ?>"
                   placeholder="Contoh: 22" required>
            <p class="text-sm text-muted mt-4">Rentang: 18-35 tahun. Optimal: 24-25 tahun.</p>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="btn btn-primary">Simpan Data Diri</button>
            <a href="user_dashboard.php" class="btn btn-outline">Kembali</a>
        </div>
    </form>
</div>

<?php renderFooter(); ?>
