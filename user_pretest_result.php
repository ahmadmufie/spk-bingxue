<?php
session_start();
require_once 'config.php';
require_once 'partials/header.php';
requireUser();

if (!isset($_SESSION['pretest_result'])) {
    redirect('user_dashboard.php');
}

$result  = $_SESSION['pretest_result'];
$score   = $result['score'];
$correct = $result['correct'];
$total   = $result['total'];
unset($_SESSION['pretest_result']);

renderHeader('Hasil Pre-Test', 'user');
renderNav('user', 'user_pretest.php');
?>

<div class="page-header">
    <h1>Hasil Pre-Test</h1>
    <p>Berikut adalah hasil tes Anda.</p>
</div>

<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="score-result">
        <div class="score-circle" style="--pct:<?= (int)$score ?>">
            <span class="score-number"><?= number_format($score, 0) ?></span>
        </div>
        <div class="status-text" style="color:<?= $score >= 80 ? 'var(--success)' : ($score >= 50 ? 'var(--warning)' : 'var(--danger)') ?>">
            <?php
            if ($score >= 80) echo 'Luar Biasa!';
            elseif ($score >= 50) echo 'Cukup Baik';
            else echo 'Perlu Ditingkatkan';
            ?>
        </div>
        <p class="text-muted mt-4"><?= $correct ?> dari <?= $total ?> soal benar</p>
        <div class="separator"></div>
        <p class="text-sm text-muted">
            Pendaftaran Anda sudah lengkap.<br>
            Silakan tunggu keputusan dari admin.
        </p>
        <a href="user_dashboard.php" class="btn btn-primary mt-6">Kembali ke Dashboard</a>
    </div>
</div>

<?php renderFooter(); ?>
