<?php
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'webtruyen');

// Bật chế độ báo lỗi chi tiết (Rất quan trọng để debug)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (!isset($GLOBALS['conn'])) {
        $GLOBALS['conn'] = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $GLOBALS['conn']->set_charset("utf8mb4");
    }
    $conn = $GLOBALS['conn'];
} catch (Exception $e) {
    die("<h3>Lỗi kết nối Database!</h3>Chi tiết: " . $e->getMessage());
}
