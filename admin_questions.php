<?php
session_start();
require_once 'config.php';
require_once 'partials/header.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($db, $_POST['action'] ?? '');

    if ($action === 'add') {
        $text    = sanitize($db, $_POST['question_text'] ?? '');
        $optA    = sanitize($db, $_POST['option_a'] ?? '');
        $optB    = sanitize($db, $_POST['option_b'] ?? '');
        $optC    = sanitize($db, $_POST['option_c'] ?? '');
        $optD    = sanitize($db, $_POST['option_d'] ?? '');
        $correct = strtoupper(sanitize($db, $_POST['correct_answer'] ?? ''));

        if (!in_array($correct, ['A','B','C','D'])) {
            flash('Jawaban benar harus A, B, C, atau D.', 'error');
        } elseif (empty($text) || empty($optA) || empty($optB) || empty($optC) || empty($optD)) {
            flash('Semua field harus diisi.', 'error');
        } else {
            $stmt = $db->prepare("INSERT INTO questions (question_text,option_a,option_b,option_c,option_d,correct_answer) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $text, $optA, $optB, $optC, $optD, $correct);
            $stmt->execute();
            flash('Soal baru berhasil ditambahkan.', 'success');
        }
        redirect('admin_questions.php');
    }

    if ($action === 'delete') {
        $qid = (int)$_POST['qid'];
        $stmt = $db->prepare("DELETE FROM questions WHERE id=?");
        $stmt->bind_param("i", $qid);
        $stmt->execute();
        flash('Soal berhasil dihapus.', 'success');
        redirect('admin_questions.php');
    }

    if ($action === 'edit') {
        $qid     = (int)$_POST['qid'];
        $text    = sanitize($db, $_POST['question_text'] ?? '');
        $optA    = sanitize($db, $_POST['option_a'] ?? '');
        $optB    = sanitize($db, $_POST['option_b'] ?? '');
        $optC    = sanitize($db, $_POST['option_c'] ?? '');
        $optD    = sanitize($db, $_POST['option_d'] ?? '');
        $correct = strtoupper(sanitize($db, $_POST['correct_answer'] ?? ''));
        if (!empty($text)) {
            $stmt = $db->prepare("UPDATE questions SET question_text=?,option_a=?,option_b=?,option_c=?,option_d=?,correct_answer=? WHERE id=?");
            $stmt->bind_param("ssssssi", $text, $optA, $optB, $optC, $optD, $correct, $qid);
            $stmt->execute();
            flash('Soal berhasil diperbarui.', 'success');
        }
        redirect('admin_questions.php');
    }
}

$questions = $db->query("SELECT * FROM questions ORDER BY id");
$editQ     = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $editQ = $db->query("SELECT * FROM questions WHERE id=$eid")->fetch_assoc();
}

renderHeader('Bank Soal', 'admin');
renderNav('admin', 'admin_questions.php');
?>

<div class="page-header">
    <div class="breadcrumb">Admin → Bank Soal</div>
    <h1>Manajemen Bank Soal Pre-Test</h1>
    <p>Tambah, edit, atau hapus soal pilihan ganda untuk Pre-Test pelamar (C3).</p>
</div>

<div class="grid-2">
    <!-- Add/Edit Form -->
    <div class="card">
        <div class="card-title"><?= $editQ ? 'Edit Soal #' . $editQ['id'] : 'Tambah Soal Baru' ?></div>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editQ ? 'edit' : 'add' ?>">
            <?php if ($editQ): ?>
                <input type="hidden" name="qid" value="<?= $editQ['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Teks Pertanyaan</label>
                <textarea name="question_text" class="form-control" rows="3" required><?= htmlspecialchars($editQ['question_text'] ?? '') ?></textarea>
            </div>
            <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $k => $v): ?>
            <div class="form-group">
                <label class="form-label">Opsi <?= $v ?></label>
                <input type="text" name="option_<?= $k ?>" class="form-control"
                       value="<?= htmlspecialchars($editQ['option_' . $k] ?? '') ?>" required>
            </div>
            <?php endforeach; ?>
            <div class="form-group">
                <label class="form-label">Jawaban Benar</label>
                <select name="correct_answer" class="form-control" required>
                    <?php foreach (['A','B','C','D'] as $o): ?>
                    <option value="<?= $o ?>" <?= isset($editQ['correct_answer']) && $editQ['correct_answer'] === $o ? 'selected' : '' ?>><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary"><?= $editQ ? 'Perbarui Soal' : 'Tambah Soal' ?></button>
                <?php if ($editQ): ?>
                    <a href="admin_questions.php" class="btn btn-outline">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Question List -->
    <div class="card" style="max-height:75vh;overflow-y:auto">
        <div class="card-title">Daftar Soal (<?= $questions->num_rows ?> soal)</div>
        <?php
        $questions->data_seek(0);
        $no = 1;
        while ($q = $questions->fetch_assoc()):
        ?>
        <div style="border-bottom:1px solid var(--border);padding:14px 0">
            <div style="font-size:0.78rem;font-weight:700;color:var(--accent);margin-bottom:5px">SOAL <?= $no++ ?></div>
            <div style="font-size:0.875rem;color:var(--text);margin-bottom:8px"><?= htmlspecialchars(substr($q['question_text'], 0, 100)) ?>...</div>
            <div style="font-size:0.78rem;color:var(--success);margin-bottom:8px">✓ Jawaban: <?= $q['correct_answer'] ?></div>
            <div class="flex gap-2">
                <a href="admin_questions.php?edit=<?= $q['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <form method="POST" style="display:inline" onsubmit="return confirm('Hapus soal ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="qid" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php renderFooter(); ?>
