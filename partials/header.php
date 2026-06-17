<?php
function renderHeader($title = '', $role = 'user') {
    $appName = APP_NAME;
    $flash = getFlash();
    $flashHtml = '';
    if ($flash) {
        $cls = $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'error' ? 'alert-error' : 'alert-info');
        $flashHtml = '<div class="alert ' . $cls . '">' . htmlspecialchars($flash['msg']) . '</div>';
    }
    $pageTitle = $title ? htmlspecialchars($title) . ' - ' . $appName : $appName;
    echo '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $pageTitle . '</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
</head>
<body class="role-' . $role . '">
' . $flashHtml;
}

function renderNav($role = 'user', $activePage = '') {
    $appName = APP_NAME;
    if ($role === 'admin') {
        $links = [
            'admin_dashboard.php'   => ['icon' => '◈', 'label' => 'Dashboard SAW'],
            'admin_criteria.php'    => ['icon' => '⊞', 'label' => 'Kelola Kriteria'],
            'admin_questions.php'   => ['icon' => '❓', 'label' => 'Bank Soal'],
            'admin_employees.php'   => ['icon' => '👥', 'label' => 'Data Karyawan'],
        ];
    } else {
        $links = [
            'user_dashboard.php'    => ['icon' => '◈', 'label' => 'Dashboard'],
            'user_personal.php'     => ['icon' => '✎', 'label' => 'Data Diri'],
            'user_assessment.php'   => ['icon' => '★', 'label' => 'Self-Assessment'],
            'user_pretest.php'      => ['icon' => '📝', 'label' => 'Pre-Test'],
        ];
    }
    $nav = '<nav class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">B</span>
            <span class="brand-text">Bingxue</span>
        </div>
        <ul class="nav-list">';
    foreach ($links as $href => $item) {
        $activeClass = (basename($_SERVER['PHP_SELF']) === $href) ? ' active' : '';
        $nav .= '<li><a href="' . $href . '" class="nav-link' . $activeClass . '">
            <span class="nav-icon">' . $item['icon'] . '</span>
            <span>' . $item['label'] . '</span>
        </a></li>';
    }
    $nav .= '</ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="nav-link logout-link">
                <span class="nav-icon">⬡</span>
                <span>Logout</span>
            </a>
        </div>
    </nav>
    <div class="main-content">';
    echo $nav;
}

function renderFooter() {
    echo '</div></body></html>';
}
?>
