<?php
// File: api/chat.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once dirname(__DIR__) . '/app/models/ChatModel.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$chatModel = new ChatModel();
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_inbox':
        $data = $chatModel->getInbox($user_id);
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'get_messages':
        $other_user = intval($_GET['other_user'] ?? 0);
        if ($other_user > 0) {
            $data = $chatModel->getMessages($user_id, $other_user);
            // Đánh dấu đã đọc
            $chatModel->markAsRead($user_id, $other_user);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Người dùng không hợp lệ']);
        }
        break;

    case 'send_message':
        $receiver_id = intval($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if ($receiver_id > 0 && !empty($message)) {
            $chatModel->sendMessage($user_id, $receiver_id, $message);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
        }
        break;

    case 'poll':
        $last_id = intval($_GET['last_id'] ?? 0);
        $data = $chatModel->checkNewMessages($user_id, $last_id);
        
        // Nếu client đang mở khung chat với 1 người cụ thể, tự mark_read cho người đó
        $current_chat_with = intval($_GET['current_chat_with'] ?? 0);
        if ($current_chat_with > 0 && count($data) > 0) {
            $chatModel->markAsRead($user_id, $current_chat_with);
        }

        $unread_total = $chatModel->getTotalUnread($user_id);

        echo json_encode([
            'status' => 'success', 
            'data' => $data, 
            'unread_total' => $unread_total
        ]);
        break;

    case 'get_unread_total':
        $unread_total = $chatModel->getTotalUnread($user_id);
        echo json_encode(['status' => 'success', 'unread_total' => $unread_total]);
        break;

    case 'search_users':
        $query = trim($_GET['q'] ?? '');
        if (empty($query)) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit;
        }
        
        $users = $chatModel->searchUsers($query, $user_id);
        echo json_encode(['status' => 'success', 'data' => $users]);
        break;

    case 'mark_read':
        $sender_id = intval($_POST['sender_id'] ?? 0);
        if ($sender_id > 0) {
            $chatModel->markAsRead($user_id, $sender_id);
            echo json_encode(['status' => 'success']);
        }
        break;

    case 'delete_message':
        $message_id = intval($_POST['message_id'] ?? 0);
        if ($message_id > 0) {
            if ($chatModel->deleteMessage($message_id, $user_id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Không thể xóa tin nhắn']);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
        break;
}
?>
