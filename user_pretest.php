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

if (!$app || !$app['self_assessment_filled']) {
    flash('Harap selesaikan self-assessment terlebih dahulu.', 'error');
    redirect('user_assessment.php');
}

if ($app['pretest_taken']) {
    flash('Anda sudah mengerjakan pre-test. Skor: ' . number_format($app['pretest_score'], 0), 'info');
    redirect('user_dashboard.php');
}

// Fetch questions
$qRes = $db->query("SELECT * FROM questions ORDER BY id");
$questions = [];
while ($q = $qRes->fetch_assoc()) {
    $questions[] = $q;
}
$totalQ = count($questions);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $totalQ > 0) {
    $correct = 0;
    foreach ($questions as $q) {
        $ans = strtoupper(sanitize($db, $_POST['q_' . $q['id']] ?? ''));
        if ($ans === $q['correct_answer']) {
            $correct++;
        }
    }
    $percentage = $totalQ > 0 ? round(($correct / $totalQ) * 100, 2) : 0;
    $c3score    = calcC3Score($db, $percentage);

    $stmt2 = $db->prepare("UPDATE applicants SET pretest_score=?, c3_score=?,
        pretest_taken=1, submitted_at=NOW() WHERE user_id=?");
    $stmt2->bind_param("ddi", $percentage, $c3score, $userId);
    $stmt2->execute();

    // Trigger SAW recalculation
    calculateSAW();

    $_SESSION['pretest_result'] = ['score' => $percentage, 'correct' => $correct, 'total' => $totalQ];
    redirect('user_pretest_result.php');
}

renderHeader('Pre-Test', 'user');
renderNav('user', 'user_pretest.php');
?>

<div class="page-header">
    <div class="breadcrumb">Dashboard → Pre-Test</div>
    <h1>Pre-Test Pengetahuan (C3)</h1>
    <p>Jawab <?= $totalQ ?> soal pilihan ganda. Kerjakan dengan teliti dan jujur.</p>
</div>

<div class="quiz-container">
    <?php if ($totalQ === 0): ?>
        <div class="card"><p class="text-muted text-center">Belum ada soal tersedia. Hubungi admin.</p></div>
    <?php else: ?>
    <form method="POST" id="quizForm">
        <?php foreach ($questions as $i => $q): ?>
        <div class="question-card">
            <div class="question-num">Soal <?= $i + 1 ?> dari <?= $totalQ ?></div>
            <div class="question-text"><?= htmlspecialchars($q['question_text']) ?></div>
            <div class="option-list">
                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                    <?php $optKey = 'option_' . strtolower($opt); ?>
                    <label class="option-label">
                        <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $opt ?>" required>
                        <div class="option-key"><?= $opt ?></div>
                        <span class="option-content"><?= htmlspecialchars($q[$optKey]) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="card flex-between" style="margin-top:8px;">
            <div class="text-sm text-muted">Pastikan semua soal telah dijawab sebelum submit.</div>
            <button type="submit" class="btn btn-primary">Kumpulkan Jawaban →</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
