<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spk_bingxue');
define('APP_NAME', 'SPK Bingxue Rancaekek');
define('APP_VERSION', '2.0');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:40px;color:#c0392b;">
                <h2>Database Connection Error</h2>
                <p>Could not connect to MySQL: ' . $conn->connect_error . '</p>
                <p>Please ensure:<br>1. XAMPP MySQL is running<br>2. You have imported <code>database.sql</code></p>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function sanitize($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect('login.php');
    }
}

function requireUser() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        redirect('login.php');
    }
}

// SAW Helper: get sub-criteria score by criteria code and label match
function getSubScore($conn, $criteriaCode, $label) {
    $code = sanitize($conn, $criteriaCode);
    $lbl  = sanitize($conn, $label);
    $sql  = "SELECT sc.value FROM sub_criteria sc
             JOIN criteria c ON sc.criteria_id = c.id
             WHERE c.code = '$code' AND sc.label = '$lbl' LIMIT 1";
    $res  = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        return (float)$res->fetch_assoc()['value'];
    }
    return 0;
}
?>
