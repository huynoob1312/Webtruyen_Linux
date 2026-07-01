<?php
// File: api/user.php
// Quản lý thông tin, lịch sử, yêu thích, thông báo của User

require_once '../app/models/UserModel.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header("Content-Type: application/json; charset=UTF-8");

function response($status, $msg, $data = []) {
    echo json_encode(['status' => $status, 'message' => $msg, 'data' => $data]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    response('error', 'Bạn chưa đăng nhập');
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userModel = new UserModel();

switch ($action) {
    // ==========================================
    // 1. LỊCH SỬ ĐỌC (HISTORY)
    // ==========================================
    case 'history_add':
        $type = $_POST['type'] ?? '';
        $item_id = $_POST['item_id'] ?? '';
        $item_name = $_POST['item_name'] ?? '';
        $item_image = $_POST['item_image'] ?? '';
        $chapter_name = $_POST['chapter_name'] ?? '';
        $chapter_url = $_POST['chapter_url'] ?? '';

        if($type && $item_id) {
            if ($userModel->addHistory($user_id, $type, $item_id, $item_name, $item_image, $chapter_name, $chapter_url)) {
                response('success', 'Đã lưu lịch sử');
            } else {
                response('error', 'Lỗi lưu lịch sử');
            }
        } else {
            response('error', 'Thiếu dữ liệu');
        }
        break;

    case 'history_get':
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
        $data = $userModel->getHistory($user_id, $limit);
        response('success', '', $data);
        break;

    case 'history_delete_one':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            if ($userModel->deleteHistoryOne($user_id, $id)) { 
                response('success', 'Đã xóa khỏi lịch sử'); 
            } else { 
                response('error', 'Lỗi cơ sở dữ liệu'); 
            }
        } else {
            response('error', 'ID không hợp lệ');
        }
        break;

    case 'history_delete_all':
        if ($userModel->deleteHistoryAll($user_id)) { 
            response('success', 'Đã xóa toàn bộ lịch sử'); 
        } else { 
            response('error', 'Lỗi cơ sở dữ liệu'); 
        }
        break;

    // ==========================================
    // 2. YÊU THÍCH (FAVORITE)
    // ==========================================
    case 'toggle_favorite':
        $type = $_POST['type'] ?? 'novel';
        if ($type === 'novel') {
            $novel_id = intval($_POST['id']);
            $res = $userModel->toggleFavoriteNovel($user_id, $novel_id);
            if ($res === 'removed') response('removed', 'Đã bỏ thích truyện chữ!');
            else response('added', 'Đã lưu truyện chữ!');
        } elseif ($type === 'comic') {
            $slug = $_POST['slug'] ?? '';
            $name = $_POST['name'] ?? '';
            $thumb = $_POST['thumb'] ?? '';
            $res = $userModel->toggleFavoriteComic($user_id, $slug, $name, $thumb);
            if ($res === 'removed') response('removed', 'Đã bỏ thích truyện tranh!');
            else response('added', 'Đã lưu truyện tranh!');
        }
        break;

    // ==========================================
    // 3. THÔNG BÁO (NOTIFICATION)
    // ==========================================
    case 'notif_report':
        $title = trim($_POST['title'] ?? '');
        $msg = trim($_POST['message'] ?? '');
        if (empty($title) || empty($msg)) response('error', 'Vui lòng nhập đủ thông tin');
        
        if ($userModel->notifyAdmins($user_id, $title, $msg)) {
            response('success', 'Đã gửi báo lỗi tới BQT!');
        } else {
            response('error', 'Không tìm thấy Admin nào để gửi.');
        }
        break;

    case 'notif_get':
        $data = $userModel->getNotifications($user_id);
        response('success', '', $data);
        break;

    case 'notif_mark_read':
        $notif_id = intval($_POST['id']);
        $userModel->markNotifRead($user_id, $notif_id);
        response('success', 'Đã đọc');
        break;

    case 'notif_delete':
        $notif_id = intval($_POST['id']);
        $userModel->deleteNotif($user_id, $notif_id);
        response('success', 'Đã xóa');
        break;

    case 'notif_delete_all':
        if ($userModel->deleteAllNotifs($user_id)) { 
            response('success', 'Đã xóa tất cả thông báo'); 
        } else { 
            response('error', 'Lỗi khi xóa'); 
        }
        break;

    default:
        response('error', 'Hành động không hợp lệ');
        break;
}
?>