<?php
// File: api/auth.php
// Đăng nhập, đăng ký, đăng xuất qua API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../app/models/UserModel.php';

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing action']);
    exit;
}

$action = $_POST['action'];
$userModel = new UserModel();

switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $res = $userModel->login($username, $password);
        if ($res['status'] === 'success') {
            $user = $res['data'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : null;
            echo json_encode(['status' => 'success', 'message' => 'Đăng nhập thành công', 'data' => ['role' => $user['role']]]);
        } else {
            echo json_encode($res);
        }
        break;

    case 'register':
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_pass) {
            echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!']);
            break;
        }

        $res = $userModel->register($username, $email, $password);
        echo json_encode($res);
        break;
        
    case 'logout':
        session_unset();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Đã đăng xuất']);
        break;
        
    case 'check_session':
        if (isset($_SESSION['user_id'])) {
            $check_id = $_SESSION['user_id'];
            $u_data = $userModel->checkSessionStatus($check_id);

            if ($u_data) {
                if ($u_data['status'] === 'banned') {
                    session_unset();
                    session_destroy();
                    echo json_encode(['status' => 'error', 'message' => 'banned']);
                    exit;
                } else {
                    if ($_SESSION['role'] !== $u_data['role']) {
                        $_SESSION['role'] = $u_data['role'];
                    }
                    
                    echo json_encode([
                        'status' => 'success', 
                        'data' => [
                            'user_id' => $_SESSION['user_id'],
                            'username' => $_SESSION['username'],
                            'role' => $_SESSION['role'],
                            'avatar' => $u_data['avatar']
                        ]
                    ]);
                    exit;
                }
            } else {
                session_unset();
                session_destroy();
                echo json_encode(['status' => 'error', 'message' => 'not_found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
