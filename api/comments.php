<?php
// File: api/comments.php
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json; charset=utf-8');

require_once '../app/models/CommentModel.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$action = $_POST['action'] ?? (isset($_GET['action']) ? $_GET['action'] : '');

function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $ago = new DateTime($datetime, new DateTimeZone('Asia/Ho_Chi_Minh'));
        $diff = $now->diff($ago);
        $w = floor($diff->d / 7);
        $diff->d -= $w * 7;
        $string = ['y' => 'năm','m' => 'tháng','w' => 'tuần','d' => 'ngày','h' => 'giờ','i' => 'phút','s' => 'giây'];
        foreach ($string as $k => &$v) {
            if ($diff->$k) { $v = $diff->$k . ' ' . $v; } else { unset($string[$k]); }
        }
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' trước' : 'vừa xong';
    } catch (Exception $e) {
        return 'vừa xong';
    }
}

$commentModel = new CommentModel();

switch ($action) {
    case 'add':
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập!']); 
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $content = trim(htmlspecialchars($_POST['content'] ?? ''));
        $type = $_POST['type'] ?? 'novel';
        $obj_id = $_POST['obj_id'] ?? 0;
        $parent_id = intval($_POST['parent_id'] ?? 0);

        if (empty($content)) {
            echo json_encode(['status' => 'error', 'message' => 'Nội dung trống!']);
            exit;
        }

        if ($commentModel->addComment($user_id, $type, $obj_id, $content, $parent_id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi DB khi thêm bình luận']);
        }
        break;

    case 'list':
        $type = $_POST['type'] ?? (isset($_GET['type']) ? $_GET['type'] : 'novel');
        $obj_id = $_POST['obj_id'] ?? (isset($_GET['obj_id']) ? $_GET['obj_id'] : 0);
        $user_current = $_SESSION['user_id'] ?? 0;

        $comments_raw = $commentModel->getCommentsList($type, $obj_id, $user_current);
        
        $comments = [];
        foreach ($comments_raw as $row) {
            $row['time_ago'] = time_elapsed_string($row['created_at']);
            $comments[] = $row;
        }

        $tree = [];
        $map = [];
        foreach ($comments as $cmt) {
            $cmt['replies'] = [];
            $map[$cmt['id']] = $cmt;
        }
        foreach ($comments as $cmt) {
            if ($cmt['parent_id'] != 0) {
                if (isset($map[$cmt['parent_id']])) {
                    $map[$cmt['parent_id']]['replies'][] = &$map[$cmt['id']];
                }
            } else {
                $tree[] = &$map[$cmt['id']];
            }
        }
        echo json_encode(['status' => 'success', 'data' => array_values($tree)]);
        break;

    case 'delete':
        if (!isset($_SESSION['user_id'])) { echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']); exit; }
        $cmt_id = intval($_POST['cmt_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'user';

        if ($commentModel->deleteComment($cmt_id, $user_id, $role)) {
            echo json_encode(['status' => 'success']);
        } else { 
            echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền xóa hoặc Lỗi DB']); 
        }
        break;

    case 'edit':
        if (!isset($_SESSION['user_id'])) { echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']); exit; }
        $cmt_id = intval($_POST['cmt_id'] ?? 0);
        $content = trim(htmlspecialchars($_POST['content'] ?? ''));
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'user';

        if (empty($content)) { echo json_encode(['status' => 'error', 'message' => 'Nội dung trống']); exit; }

        if ($commentModel->editComment($cmt_id, $user_id, $content, $role)) {
            echo json_encode(['status' => 'success']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Không có quyền sửa hoặc Lỗi DB']); 
        }
        break;

    case 'like':
        if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error']); exit; }
        $uid = $_SESSION['user_id'];
        $cmt_id = intval($_POST['cmt_id'] ?? 0);

        if ($cmt_id > 0) {
            $commentModel->toggleLike($cmt_id, $uid);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
