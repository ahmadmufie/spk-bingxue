<?php
session_start();
require_once 'config.php';
require_once 'saw_calculation.php';
require_once 'partials/header.php';
requireAdmin();

$db = getDB();

// Handle Accept/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['app_id'])) {
        $appId  = (int)$_POST['app_id'];
        $action = sanitize($db, $_POST['action']);

        if ($action === 'accept') {
            $stmt = $db->prepare("UPDATE applicants SET status='accepted' WHERE id=?");
            $stmt->bind_param("i", $appId);
            $stmt->execute();

            // Create employee record
            $appData = $db->query("SELECT a.*, u.name FROM applicants a JOIN users u ON a.user_id=u.id WHERE a.id=$appId")->fetch_assoc();
            if ($appData) {
                $stmt2 = $db->prepare("INSERT INTO employees (user_id, applicant_id, name, position, join_date, status)
                                       SELECT user_id, id, ?, 'Staff Baru', CURDATE(), 'active'
                                       FROM applicants WHERE id=?
                                       ON DUPLICATE KEY UPDATE status='active'");
                $name = sanitize($db, $appData['name']);
                $stmt2->bind_param("si", $name, $appId);
                $stmt2->execute();
            }
            flash('Pelamar berhasil DITERIMA dan dicatat sebagai karyawan.', 'success');

        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE applicants SET status='rejected' WHERE id=?");
            $stmt->bind_param("i", $appId);
            $stmt->execute();
            flash('Pelamar telah DITOLAK.', 'error');

        } elseif ($action === 'recalculate') {
            $count = calculateSAW();
            flash("SAW berhasil direcalculate untuk $count pelamar.", 'success');
        }
        redirect('admin_dashboard.php');
    }
}

// Stats
$totalApplicants = $db->query("SELECT COUNT(*) as c FROM applicants")->fetch_assoc()['c'];
$totalReady      = $db->query("SELECT COUNT(*) as c FROM applicants WHERE personal_data_filled=1 AND self_assessment_filled=1 AND pretest_taken=1")->fetch_assoc()['c'];
$totalAccepted   = $db->query("SELECT COUNT(*) as c FROM applicants WHERE status='accepted'")->fetch_assoc()['c'];
$totalRejected   = $db->query("SELECT COUNT(*) as c FROM applicants WHERE status='rejected'")->fetch_assoc()['c'];
$totalPending    = $db->query("SELECT COUNT(*) as c FROM applicants WHERE status='pending' AND personal_data_filled=1 AND self_assessment_filled=1 AND pretest_taken=1")->fetch_assoc()['c'];

// Fetch SAW results (complete applicants)
$sawResults = $db->query("
    SELECT a.*, u.name, u.email,
           a.c1_score, a.c2_score, a.c3_score, a.c4_score, a.c5_score,
           a.saw_value, a.rank, a.status
    FROM applicants a
    JOIN users u ON a.user_id = u.id
    WHERE a.personal_data_filled = 1
      AND a.self_assessment_filled = 1
      AND a.pretest_taken = 1
    ORDER BY a.rank ASC, a.saw_value DESC
");

// Fetch criteria for display
$critRes = $db->query("SELECT * FROM criteria ORDER BY code");
$criteria = [];
while ($c = $critRes->fetch_assoc()) $criteria[] = $c;

// Incomplete applicants
$incomplete = $db->query("
    SELECT u.name, u.email, a.personal_data_filled, a.self_assessment_filled, a.pretest_taken
    FROM applicants a
    JOIN users u ON a.user_id = u.id
    WHERE NOT (a.personal_data_filled=1 AND a.self_assessment_filled=1 AND a.pretest_taken=1)
    ORDER BY u.name
");

renderHeader('Dashboard SAW', 'admin');
renderNav('admin', 'admin_dashboard.php');
?>

<div class="page-header flex-between">
    <div>
        <div class="breadcrumb">Admin</div>
        <h1>Dashboard SAW — Pemeringkatan Pelamar</h1>
        <p>Sistem otomatis menghitung nilai SAW. Admin tinggal klik Terima atau Tolak.</p>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="recalculate">
        <button type="submit" class="btn btn-outline">↻ Recalculate SAW</button>
    </form>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Total Pelamar</div>
        <div class="stat-value"><?= $totalApplicants ?></div>
        <div class="stat-sub">Terdaftar</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Siap Diproses</div>
        <div class="stat-value"><?= $totalReady ?></div>
        <div class="stat-sub">Data lengkap</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Diterima</div>
        <div class="stat-value"><?= $totalAccepted ?></div>
        <div class="stat-sub">Karyawan aktif</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Ditolak</div>
        <div class="stat-value"><?= $totalRejected ?></div>
        <div class="stat-sub">Tidak lolos seleksi</div>
    </div>
</div>

<!-- SAW Matrix Table -->
<div class="card mb-6">
    <div class="card-title flex-between">
        <span>Matriks SAW & Peringkat Pelamar</span>
        <span class="badge badge-info"><?= $totalReady ?> pelamar lengkap</span>
    </div>
    <div class="table-wrap">
        <table class="saw-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Nama</th>
                    <th title="Skill - W:30%">C1 (Skill)</th>
                    <th title="Pengalaman - W:25%">C2 (Exp)</th>
                    <th title="Pre-Test - W:20%">C3 (Test)</th>
                    <th title="Pendidikan - W:15%">C4 (Edu)</th>
                    <th title="Umur - W:10%">C5 (Age)</th>
                    <th>Nilai SAW (Vi)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($sawResults && $sawResults->num_rows > 0): ?>
                <?php while ($row = $sawResults->fetch_assoc()):
                    $rank = (int)$row['rank'];
                    $rankClass = $rank === 1 ? 'rank-gold' : ($rank === 2 ? 'rank-silver' : ($rank === 3 ? 'rank-bronze' : 'rank-other'));
                    $rowClass  = $rank === 1 ? 'rank-1' : '';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><span class="saw-rank <?= $rankClass ?>"><?= $rank ?></span></td>
                    <td>
                        <div style="font-weight:600"><?= htmlspecialchars($row['name']) ?></div>
                        <div class="text-sm text-muted"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td><?= number_format($row['c1_score'], 1) ?></td>
                    <td><?= number_format($row['c2_score'], 1) ?></td>
                    <td><?= number_format($row['c3_score'], 1) ?><span class="text-muted text-sm"> (<?= number_format($row['pretest_score'], 0) ?>%)</span></td>
                    <td><?= number_format($row['c4_score'], 1) ?></td>
                    <td><?= number_format($row['c5_score'], 1) ?></td>
                    <td style="font-weight:700;color:var(--accent)"><?= number_format($row['saw_value'] * 100, 1) ?></td>
                    <td>
                        <?php
                        $s = $row['status'];
                        if ($s === 'accepted')      echo '<span class="badge badge-accepted">Diterima</span>';
                        elseif ($s === 'rejected')  echo '<span class="badge badge-rejected">Ditolak</span>';
                        else                        echo '<span class="badge badge-pending">Pending</span>';
                        ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                        <div class="flex gap-2">
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Terima</button>
                            </form>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pelamar ini?')">Tolak</button>
                            </form>
                        </div>
                        <?php else: ?>
                            <span class="text-muted text-sm">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center text-muted" style="padding:30px">Belum ada pelamar yang menyelesaikan semua tahap.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Criteria Weights Display -->
<div class="card mb-6">
    <div class="card-title">Bobot Kriteria SAW (Aktif)</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th>Bobot (%)</th>
                    <th>Tipe</th>
                    <th>Bobot Ternormalisasi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $totalW = array_sum(array_column($criteria, 'weight'));
            foreach ($criteria as $c):
                $norm = $totalW > 0 ? round(((float)$c['weight'] / $totalW) * 100, 2) : 0;
            ?>
            <tr>
                <td><span class="criteria-code"><?= htmlspecialchars($c['code']) ?></span></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><strong><?= number_format($c['weight'], 1) ?>%</strong></td>
                <td><span class="badge badge-info"><?= ucfirst($c['type']) ?></span></td>
                <td><?= $norm ?>%</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <a href="admin_criteria.php" class="btn btn-outline btn-sm">⚙ Kelola Kriteria & Bobot</a>
    </div>
</div>

<!-- Incomplete Applicants -->
<?php if ($incomplete && $incomplete->num_rows > 0): ?>
<div class="card">
    <div class="card-title">Pelamar Belum Lengkap</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Data Diri</th><th>Self-Assessment</th><th>Pre-Test</th></tr></thead>
            <tbody>
            <?php while ($r = $incomplete->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= $r['personal_data_filled'] ? '<span class="badge badge-accepted">✓</span>' : '<span class="badge badge-rejected">✗</span>' ?></td>
                <td><?= $r['self_assessment_filled'] ? '<span class="badge badge-accepted">✓</span>' : '<span class="badge badge-rejected">✗</span>' ?></td>
                <td><?= $r['pretest_taken'] ? '<span class="badge badge-accepted">✓</span>' : '<span class="badge badge-rejected">✗</span>' ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
