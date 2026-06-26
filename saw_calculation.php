<?php
require_once 'config.php';

/**
 * Calculate SAW scores for all applicants dynamically from database.
 * Steps:
 * 1. Fetch all criteria and their weights.
 * 2. For each applicant, their C1-C5 raw scores are already stored.
 * 3. Normalize: Rij = Xij / max(Xj) for benefit criteria.
 * 4. Vi = sum(Wj * Rij)
 * 5. Update applicants table with saw_value and rank.
 */
function calculateSAW() {
    $db = getDB();

    // 1. Fetch criteria weights (normalized to decimal fractions summing to 1)
    $critRes = $db->query("SELECT code, weight FROM criteria ORDER BY code");
    $weights = [];
    $totalWeight = 0;
    while ($row = $critRes->fetch_assoc()) {
        $weights[$row['code']] = (float)$row['weight'];
        $totalWeight += (float)$row['weight'];
    }
    // Normalize weights (in case they don't sum to 100)
    foreach ($weights as $code => $w) {
        $weights[$code] = ($totalWeight > 0) ? ($w / $totalWeight) : 0;
    }

    // 2. Get all applicants who have completed all steps
    $res = $db->query("
        SELECT a.id, a.user_id,
               a.c1_score, a.c2_score, a.c3_score, a.c4_score, a.c5_score
        FROM applicants a
        WHERE a.personal_data_filled = 1
          AND a.self_assessment_filled = 1
          AND a.pretest_taken = 1
    ");

    if (!$res || $res->num_rows === 0) {
        return 0;
    }

    $applicants = [];
    while ($row = $res->fetch_assoc()) {
        $applicants[] = $row;
    }

    if (empty($applicants)) return 0;

    // 3. Find max values for normalization (benefit criteria: divide by max)
    $maxC1 = $maxC2 = $maxC3 = $maxC4 = $maxC5 = 0;
    foreach ($applicants as $a) {
        if ((float)$a['c1_score'] > $maxC1) $maxC1 = (float)$a['c1_score'];
        if ((float)$a['c2_score'] > $maxC2) $maxC2 = (float)$a['c2_score'];
        if ((float)$a['c3_score'] > $maxC3) $maxC3 = (float)$a['c3_score'];
        if ((float)$a['c4_score'] > $maxC4) $maxC4 = (float)$a['c4_score'];
        if ((float)$a['c5_score'] > $maxC5) $maxC5 = (float)$a['c5_score'];
    }

    // Prevent division by zero
    $maxC1 = $maxC1 ?: 1;
    $maxC2 = $maxC2 ?: 1;
    $maxC3 = $maxC3 ?: 1;
    $maxC4 = $maxC4 ?: 1;
    $maxC5 = $maxC5 ?: 1;

    // 4. Calculate Vi for each applicant
    $scored = [];
    foreach ($applicants as $a) {
        $r1 = (float)$a['c1_score'] / $maxC1;
        $r2 = (float)$a['c2_score'] / $maxC2;
        $r3 = (float)$a['c3_score'] / $maxC3;
        $r4 = (float)$a['c4_score'] / $maxC4;
        $r5 = (float)$a['c5_score'] / $maxC5;

        $vi = ($weights['C1'] * $r1)
            + ($weights['C2'] * $r2)
            + ($weights['C3'] * $r3)
            + ($weights['C4'] * $r4)
            + ($weights['C5'] * $r5);

        $scored[] = [
            'id'         => $a['id'],
            'c1_norm'    => round($r1, 4),
            'c2_norm'    => round($r2, 4),
            'c3_norm'    => round($r3, 4),
            'c4_norm'    => round($r4, 4),
            'c5_norm'    => round($r5, 4),
            'saw_value'  => round($vi, 6),
        ];
    }

    // 5. Sort by saw_value DESC and assign ranks
    usort($scored, function($a, $b) {
        return $b['saw_value'] <=> $a['saw_value'];
    });

    $rank = 1;
    foreach ($scored as $s) {
        $stmt = $db->prepare("UPDATE applicants SET saw_value = ?, `rank` = ? WHERE id = ?");
        $stmt->bind_param("dii", $s['saw_value'], $rank, $s['id']);
        $stmt->execute();
        $rank++;
    }

    return count($scored);
}

/**
 * Get C1 score from self-assessment scores dynamically using sub-criteria weights
 * Returns 0-100 (weighted average of 4 skill components)
 */
function calcC1Score($db, $comm, $coop, $ethics, $tech) {
    // Get weights from sub_criteria table (in percentages)
    $getVal = function($label) use ($db) {
        $lbl = sanitize($db, $label);
        $r = $db->query("SELECT sc.value FROM sub_criteria sc
                         JOIN criteria c ON sc.criteria_id = c.id
                         WHERE c.code = 'C1' AND sc.label LIKE '%$lbl%' LIMIT 1");
        if ($r && $r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
        return 0;
    };
    $wComm   = $getVal('Komunikasi');      // 40
    $wCoop   = $getVal('Kerjasama');       // 30
    $wEthics = $getVal('Etika');          // 20
    $wTech   = $getVal('Teknis');         // 10
    $total   = $wComm + $wCoop + $wEthics + $wTech;
    if ($total == 0) return 0;
    // Weighted average: (40% * comm + 30% * coop + 20% * ethics + 10% * tech) / 100
    $score = (($wComm * $comm) + ($wCoop * $coop) + ($wEthics * $ethics) + ($wTech * $tech)) / $total;
    return round(min(100, max(0, $score)), 2);  // Clamp to 0-100
}

/**
 * Get C2 score from experience string
 * Returns 0-100: >3 Tahun=100, 3 Tahun=75, 2 Tahun=50, 1 Tahun=25, Tidak Ada=0
 */
function calcC2Score($db, $experience) {
    $exp = sanitize($db, $experience);
    $r   = $db->query("SELECT sc.value FROM sub_criteria sc
                       JOIN criteria c ON sc.criteria_id = c.id
                       WHERE c.code = 'C2' AND sc.label = '$exp' LIMIT 1");
    if ($r && $r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
    return 0;  // Default: no experience
}

/**
 * Get C3 score from pre-test numeric score
 * Returns 70 (80-100), 20 (50-79), atau 10 (0-49)
 */
function calcC3Score($db, $pretestScore) {
    $score = (float)$pretestScore;
    if ($score >= 80) {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C3' AND sc.label LIKE '%80%' LIMIT 1");
    } elseif ($score >= 50) {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C3' AND sc.label LIKE '%50%' LIMIT 1");
    } else {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C3' AND sc.label LIKE '%0-49%' LIMIT 1");
    }
    if ($r && $r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
    return 10;  // Default: nilai terendah
}

/**
 * Get C4 score from education string
 * Returns 0-100: D3/S1/S2=100, SMA/SMK=50, SMP=0
 */
function calcC4Score($db, $education) {
    $edu = sanitize($db, $education);
    $r   = $db->query("SELECT sc.value FROM sub_criteria sc
                       JOIN criteria c ON sc.criteria_id = c.id
                       WHERE c.code = 'C4' AND sc.label = '$edu' LIMIT 1");
    if ($r && $r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
    return 0;  // Default: minimum education
}

/**
 * Returns 0-100: 24-25=100, 22-23=75, 20-21=50, 18-19=25
 */
function calcC5Score($db, $age) {
    $age = (int)$age;
    if ($age >= 24 && $age <= 25) {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C5' AND sc.label LIKE '%24-25%' LIMIT 1");
    } elseif ($age >= 22 && $age <= 23) {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C5' AND sc.label LIKE '%22-23%' LIMIT 1");
    } elseif ($age >= 20 && $age <= 21) {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C5' AND sc.label LIKE '%20-21%' LIMIT 1");
    } else {
        $r = $db->query("SELECT sc.value FROM sub_criteria sc JOIN criteria c ON sc.criteria_id=c.id WHERE c.code='C5' AND sc.label LIKE '%18-19%' LIMIT 1");
    }
    if ($r && $r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
    return 0;  // Default: minimum score$r->num_rows > 0) return (float)$r->fetch_assoc()['value'];
    return 10;
}
?>
