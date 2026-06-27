<?php
// File: api/admin.php
require_once '../config/database.php';
require_once '../app/views/includes/functions.php'; // For createSlug if available, else we can inline it.

header("Content-Type: application/json; charset=UTF-8");

// --- 1. AUTHENTICATION & AUTHORIZATION ---
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role !== 'admin' && $role !== 'mod') {
    echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
    exit;
}

$is_admin = ($role === 'admin');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- Helper Functions ---
function responseJson($status, $data = [], $message = '') {
    echo json_encode(['status' => $status, 'data' => $data, 'message' => $message]);
    exit;
}

function adminOnly($is_admin) {
    if (!$is_admin) responseJson('error', [], 'Chỉ Admin mới có quyền thao tác!');
}

function createSlugFromText($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/([^a-z0-9\-]+)/', '-', $str);
    $str = trim($str, '-');
    return $str;
}

require_once '../app/models/AdminModel.php';

$adminModel = new AdminModel();

switch ($action) {
    // MODULE: DASHBOARD
    case 'dashboard_stats':
        $data = $adminModel->getDashboardStats($user_id, $role);
        $data['username'] = $_SESSION['username'];
        responseJson('success', $data);
        break;

    // MODULE: NOVELS
    case 'get_novels':
        $novels = $adminModel->getAdminNovels();
        responseJson('success', $novels);
        break;

    case 'add_novel':
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $image = trim($_POST['cover_image'] ?? '');
        $status = $_POST['status'] ?? 'ongoing';
        $slug = createSlugFromText($title);
        $cats_arr = json_decode($_POST['categories'] ?? '[]');

        if (!$title || !$author) responseJson('error', [], 'Thiếu Tên hoặc Tác giả');

        if ($adminModel->addNovel($title, $slug, $author, $desc, $image, $status, $user_id, $cats_arr)) {
            responseJson('success', [], 'Thêm truyện thành công');
        } else {
            responseJson('error', [], 'Lỗi hệ thống');
        }
        break;

    case 'delete_novel':
        $id = intval($_POST['id'] ?? 0);
        $adminModel->deleteNovel($id);
        responseJson('success', [], 'Đã xoá truyện');
        break;

    case 'get_novel':
        $id = intval($_GET['id'] ?? 0);
        $novel = $adminModel->getAdminNovel($id);
        if ($novel) {
            responseJson('success', $novel);
        } else {
            responseJson('error', [], 'Không tìm thấy truyện');
        }
        break;

    case 'update_novel':
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $image = trim($_POST['cover_image'] ?? '');
        $status = $_POST['status'] ?? 'ongoing';
        $cats_arr = json_decode($_POST['categories'] ?? '[]');

        if (!$title || !$id) responseJson('error', [], 'Thiếu thông tin bắt buộc');

        if ($adminModel->updateNovel($id, $title, $author, $desc, $image, $status, $cats_arr)) {
            responseJson('success', [], 'Cập nhật thành công');
        } else {
            responseJson('error', [], 'Lỗi hệ thống');
        }
        break;

    // MODULE: CHAPTERS
    case 'get_chapters':
        $novel_id = intval($_GET['novel_id'] ?? 0);
        $chaps = $adminModel->getAdminChapters($novel_id);
        responseJson('success', $chaps);
        break;

    case 'delete_chapter':
        $id = intval($_POST['id'] ?? 0);
        $adminModel->deleteChapter($id);
        responseJson('success', [], 'Đã xoá chương');
        break;

    case 'get_chapter':
        $id = intval($_GET['id'] ?? 0);
        $chap = $adminModel->getAdminChapter($id);
        if ($chap) {
            responseJson('success', $chap);
        } else {
            responseJson('error', [], 'Chương không tồn tại');
        }
        break;

    case 'add_chapter':
        $novel_id = intval($_POST['novel_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $order_index = floatval($_POST['order_index'] ?? 0);

        if (!$novel_id || !$title || !$content) responseJson('error', [], 'Thiếu dữ liệu');

        if ($adminModel->addChapter($novel_id, $title, $content, $order_index)) {
            responseJson('success', [], 'Thêm chương mới thành công');
        } else {
            responseJson('error', [], 'Lỗi hệ thống');
        }
        break;

    case 'update_chapter':
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $order_index = floatval($_POST['order_index'] ?? 0);

        if (!$id || !$title || !$content) responseJson('error', [], 'Thiếu dữ liệu');

        if ($adminModel->updateChapter($id, $title, $content, $order_index)) {
            responseJson('success', [], 'Cập nhật thành công');
        } else {
            responseJson('error', [], 'Lỗi hệ thống');
        }
        break;


    default:
        responseJson('error', [], 'Invalid Admin Action');
        break;
}
?>
