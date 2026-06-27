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
    default:
        response('error', 'Hành động không hợp lệ');
        break;
}
?>
