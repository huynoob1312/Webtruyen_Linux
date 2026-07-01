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

    // ==========================================
    // MODULE: DASHBOARD
    // ==========================================
    case 'dashboard_stats':
        $data = $adminModel->getDashboardStats($user_id, $role);
        $data['username'] = $_SESSION['username'];
        responseJson('success', $data);
        break;

    // ==========================================
    // MODULE: CATEGORIES
    // ==========================================
    case 'get_categories':
        $cats = $adminModel->getCategories();
        responseJson('success', $cats);
        break;

    case 'add_category':
        $name = trim($_POST['name'] ?? '');
        if (!$name) responseJson('error', [], 'Tên không hợp lệ');
        $slug = createSlugFromText($name);
        
        $res = $adminModel->addCategory($name, $slug);
        responseJson($res['status'], [], $res['message']);
        break;

    case 'delete_category':
        $id = intval($_POST['id'] ?? 0);
        $adminModel->deleteCategory($id);
        responseJson('success', [], 'Đã xoá');
        break;

    // ==========================================
    // MODULE: NOVELS
    // ==========================================
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

    // ==========================================
    // MODULE: CHAPTERS
    // ==========================================
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

    // ==========================================
    // MODULE: USERS, COMMENTS, NOTIFICATIONS
    // ==========================================
    case 'mark_notification_read':
        $id = intval($_POST['id'] ?? 0);
        // Uses the simple query directly, or we could add it to a model. We'll leave it as a model call.
        require_once '../app/models/UserModel.php';
        $userModel = new UserModel();
        $userModel->markNotifRead($user_id, $id);
        responseJson('success', [], 'Đã xử lý');
        break;

    case 'get_users':
        adminOnly($is_admin);
        $users = $adminModel->getUsers();
        responseJson('success', $users);
        break;

    case 'reset_password':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        $new_pwd = $_POST['password'] ?? '';
        if (!$id || !$new_pwd) responseJson('error', [], 'Thiếu thông tin');
        $adminModel->resetUserPassword($id, $new_pwd);
        responseJson('success', [], 'Đã đổi mật khẩu');
        break;
        
    case 'add_user':
        adminOnly($is_admin);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $pwd = trim($_POST['password'] ?? '');

        if (!$username || !$email || !$pwd) responseJson('error', [], 'Thiếu thông tin bắt buộc');
        
        $res = $adminModel->addUser($username, $email, $role, $pwd);
        responseJson($res['status'], [], $res['message']);
        break;

    case 'edit_user':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $pwd = trim($_POST['password'] ?? '');

        if (!$id || !$username || !$email) responseJson('error', [], 'Thiếu thông tin bắt buộc');
        
        if ($adminModel->editUser($id, $username, $email, $role, $pwd)) {
            if ($id == $user_id) {
                $_SESSION['role'] = $role;
                $_SESSION['username'] = $username;
            }
            responseJson('success', [], 'Cập nhật thành công');
        } else {
            responseJson('error', [], 'Lỗi hệ thống');
        }
        break;

    case 'delete_user_account':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        if ($id == $user_id) responseJson('error', [], 'Không thể tự xóa bản thân');
        $adminModel->deleteUserAccount($id);
        responseJson('success', [], 'Đã xóa tài khoản');
        break;

    case 'update_user_role':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        $new_role = $_POST['role'] ?? 'user';
        $adminModel->updateUserRole($id, $new_role);
        if ($id == $user_id) {
            $_SESSION['role'] = $new_role;
        }
        responseJson('success', [], 'Thành công');
        break;
        
    case 'toggle_user_status':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $adminModel->toggleUserStatus($id, $status);
        responseJson('success', [], 'Thành công');
        break;
        
    case 'get_comments':
        adminOnly($is_admin);
        $cmts = $adminModel->getGlobalComments();
        responseJson('success', $cmts);
        break;

    case 'delete_comment':
        adminOnly($is_admin);
        $id = intval($_POST['id'] ?? 0);
        $adminModel->deleteGlobalComment($id);
        responseJson('success', [], 'Đã xóa bình luận');
        break;

    case 'send_notification':
        adminOnly($is_admin);
        $uid = intval($_POST['user_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $msg = trim($_POST['message'] ?? '');
        
        if (!$title) responseJson('error', [], 'Bảng tin cần tiêu đề');

        $adminModel->sendSystemNotification($user_id, $uid, $title, $msg);
        responseJson('success', [], 'Đã gửi thông báo thành công');
        break;

    default:
        responseJson('error', [], 'Invalid Admin Action');
        break;
}
?>
