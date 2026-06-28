<?php
session_start();
require_once 'config.php';
require_once 'partials/header.php';
requireUser();

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Get or create applicant record
$stmt = $db->prepare("SELECT * FROM applicants WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$app = $res->fetch_assoc();

if (!$app) {
    $stmt2 = $db->prepare("INSERT INTO applicants (user_id) VALUES (?)");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $newId = $db->insert_id;
    $app = ['id' => $newId, 'user_id' => $userId, 'status' => 'pending',
            'personal_data_filled' => 0, 'self_assessment_filled' => 0,
            'pretest_taken' => 0, 'saw_value' => 0, 'rank' => 0,
            'c1_score' => 0, 'c2_score' => 0, 'c3_score' => 0,
            'c4_score' => 0, 'c5_score' => 0];
}

$step1 = (int)$app['personal_data_filled'];
$step2 = (int)$app['self_assessment_filled'];
$step3 = (int)$app['pretest_taken'];
$allDone = $step1 && $step2 && $step3;

renderHeader('Dashboard Pelamar', 'user');
renderNav('user', 'user_dashboard.php');
?>

<div class="page-header">
    <div class="breadcrumb">SPK Bingxue</div>
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <p>Lengkapi semua tahap pendaftaran untuk diproses oleh sistem SAW.</p>
</div>

<!-- Progress Steps -->
<div class="steps-bar">
    <div class="step-item <?= $step1 ? 'done' : 'active' ?>">
        <div class="step-num"><?= $step1 ? '✓' : '1' ?></div>
        <span class="step-label">Data Diri</span>
    </div>
    <div class="step-item <?= $step2 ? 'done' : ($step1 ? 'active' : '') ?>">
        <div class="step-num"><?= $step2 ? '✓' : '2' ?></div>
        <span class="step-label">Self-Assessment</span>
    </div>
    <div class="step-item <?= $step3 ? 'done' : ($step2 ? 'active' : '') ?>">
        <div class="step-num"><?= $step3 ? '✓' : '3' ?></div>
        <span class="step-label">Pre-Test</span>
    </div>
    <div class="step-item <?= $allDone ? 'done' : '' ?>">
        <div class="step-num"><?= $allDone ? '✓' : '4' ?></div>
        <span class="step-label">Menunggu Hasil</span>
    </div>
</div>

<!-- Status Card -->
<?php if ($allDone): ?>
<div class="card mb-6">
    <?php
    $status = $app['status'];
    if ($status === 'accepted') {
        echo '<div class="status-card">
            <div class="status-icon">🎉</div>
            <div class="status-text" style="color:var(--success)">Selamat! Lamaran Diterima</div>
            <p class="text-muted">Anda telah diterima sebagai karyawan. Silakan datang ke outlet untuk sesi interview.</p>
            <div class="saw-score-display">Nilai SAW: <strong>' . number_format($app['saw_value'] * 100, 2) . '%</strong> | Peringkat: <strong>#' . $app['rank'] . '</strong></div>
        </div>';
    } elseif ($status === 'rejected') {
        echo '<div class="status-card">
            <div class="status-icon">😔</div>
            <div class="status-text" style="color:var(--danger)">Maaf, Lamaran Ditolak</div>
            <p class="text-muted">Terima kasih atas partisipasi Anda. Semoga sukses di kesempatan lain.</p>
            <div class="saw-score-display">Nilai SAW: <strong>' . number_format($app['saw_value'] * 100, 2) . '%</strong> | Peringkat: <strong>#' . $app['rank'] . '</strong></div>
        </div>';
    } else {
        echo '<div class="status-card">
            <div class="status-icon">⏳</div>
            <div class="status-text" style="color:var(--warning)">Sedang Diproses</div>
            <p class="text-muted">Pendaftaran lengkap. Menunggu keputusan dari admin.</p>
            <div class="saw-score-display">Nilai SAW: <strong>' . number_format($app['saw_value'] * 100, 2) . '%</strong> | Peringkat: <strong>#' . ($app['rank'] ?: '-') . '</strong></div>
        </div>';
    }
    ?>
</div>
<?php endif; ?>

<!-- Score Summary -->
<?php if ($allDone): ?>
<div class="card mb-6">
    <div class="card-title">Ringkasan Nilai Kriteria</div>
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">C1 - Skill</div>
            <div class="stat-value"><?= number_format($app['c1_score'], 1) ?></div>
            <div class="stat-sub">Nilai Keterampilan</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">C2 - Pengalaman</div>
            <div class="stat-value"><?= number_format($app['c2_score'], 1) ?></div>
            <div class="stat-sub">Pengalaman Kerja: <?= htmlspecialchars($app['experience_years'] ?? '-') ?></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">C3 - Pre-Test</div>
            <div class="stat-value"><?= number_format($app['pretest_score'], 0) ?></div>
            <div class="stat-sub">Skor: <?= number_format($app['c3_score'], 1) ?></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">C4 - Pendidikan</div>
            <div class="stat-value"><?= number_format($app['c4_score'], 1) ?></div>
            <div class="stat-sub"><?= htmlspecialchars($app['education'] ?? '-') ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">C5 - Umur</div>
            <div class="stat-value"><?= number_format($app['c5_score'], 1) ?></div>
            <div class="stat-sub"><?= htmlspecialchars($app['age'] ?? '-') ?> Tahun</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Action Cards -->
<div class="grid-3">
    <div class="card">
        <div class="card-title">📋 Data Diri</div>
        <p class="text-sm text-muted mb-4">Isi data pendidikan, pengalaman, dan usia Anda (C2, C4, C5).</p>
        <?php if ($step1): ?>
            <span class="badge badge-accepted">✓ Selesai</span>
            <a href="user_personal.php" class="btn btn-outline btn-sm" style="margin-left:8px;">Edit</a>
        <?php else: ?>
            <a href="user_personal.php" class="btn btn-primary btn-sm">Isi Sekarang</a>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-title">⭐ Self-Assessment</div>
        <p class="text-sm text-muted mb-4">Nilai diri Anda pada aspek kemampuan kerja, SOP, ketelitian, pelayanan pelanggan, dan kerja tim (C1).</p>
        <?php if ($step2): ?>
            <span class="badge badge-accepted">✓ Selesai</span>
            <a href="user_assessment.php" class="btn btn-outline btn-sm" style="margin-left:8px;">Edit</a>
        <?php elseif ($step1): ?>
            <a href="user_assessment.php" class="btn btn-primary btn-sm">Isi Sekarang</a>
        <?php else: ?>
            <span class="btn btn-outline btn-sm disabled">Selesaikan Data Diri dulu</span>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-title">📝 Pre-Test</div>
        <p class="text-sm text-muted mb-4">Kerjakan soal pilihan ganda untuk penilaian C3.</p>
        <?php if ($step3): ?>
            <span class="badge badge-accepted">✓ Selesai (<?= number_format($app['pretest_score'], 0) ?>)</span>
        <?php elseif ($step2): ?>
            <a href="user_pretest.php" class="btn btn-primary btn-sm">Mulai Tes</a>
        <?php else: ?>
            <span class="btn btn-outline btn-sm disabled">Selesaikan Self-Assessment dulu</span>
        <?php endif; ?>
    </div>
</div>

<?php renderFooter(); ?>
