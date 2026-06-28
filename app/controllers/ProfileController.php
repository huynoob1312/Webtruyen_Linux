<?php
// File: app/controllers/ProfileController.php
require_once 'core/Controller.php';
require_once 'app/models/UserModel.php';

class ProfileController extends Controller {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // 1. Xác định Profile nào cần xem
        $current_user_id = $_SESSION['user_id'] ?? 0;
        $profile_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : $current_user_id;

        if ($profile_id == 0) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $userModel = new UserModel();
        $user = $userModel->getProfileById($profile_id);

        if (!$user) {
            die("<div class='container mt-5 alert alert-danger'>Thành viên không tồn tại! <a href='index.php'>Về trang chủ</a></div>");
        }

        $is_owner = ($current_user_id === $profile_id);
        $msg = '';
        $msg_type = '';

        // 2. Xử lý các form POST (Chỉ chủ tài khoản mới được làm)
        if ($is_owner && $_SERVER['REQUEST_METHOD'] == 'POST') {

            // A. Upload Avatar File
            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                $ext = pathinfo($_FILES["avatar_file"]["name"], PATHINFO_EXTENSION);
                $new_name = "avatar_" . $profile_id . "_" . time() . "." . $ext;
                $target = $target_dir . $new_name;

                if (getimagesize($_FILES["avatar_file"]["tmp_name"]) !== false && move_uploaded_file($_FILES["avatar_file"]["tmp_name"], $target)) {
                    $userModel->updateAvatar($profile_id, $target);
                    $_SESSION['avatar'] = $target;
                    $user['avatar'] = $target;
                    $msg = "Đổi ảnh thành công!"; $msg_type = "success";
                } else {
                    $msg = "Lỗi upload ảnh."; $msg_type = "danger";
                }

            // B. Cập nhật Avatar từ Link
            } elseif (!empty($_POST['avatar_url'])) {
                $url = trim($_POST['avatar_url']);
                $userModel->updateAvatar($profile_id, $url);
                $_SESSION['avatar'] = $url;
                $user['avatar'] = $url;
                $msg = "Đã cập nhật ảnh từ link!"; $msg_type = "success";

            // C. Cập nhật Thông tin
            } elseif (isset($_POST['update_profile'])) {
                $name = trim($_POST['username']);
                $email = trim($_POST['email']);
                $result = $userModel->updateProfileInfo($profile_id, $name, $email);
                $msg = $result['message'];
                $msg_type = ($result['status'] === 'success') ? 'success' : 'danger';
                if ($result['status'] === 'success') {
                    $_SESSION['username'] = $name;
                    $user['username'] = $name;
                    $user['email'] = $email;
                }

            // D. Đổi Mật Khẩu
            } elseif (isset($_POST['change_pass'])) {
                $curr = $_POST['current_password'];
                $new  = $_POST['new_password'];
                $cf   = $_POST['confirm_password'];
                if ($new !== $cf) {
                    $msg = "Mật khẩu xác nhận không khớp!"; $msg_type = "danger";
                } else {
                    $result = $userModel->changePassword($profile_id, $curr, $new, $user['password']);
                    $msg = $result['message'];
                    $msg_type = ($result['status'] === 'success') ? 'success' : 'danger';
                }
            }
        }

        // 3. Lấy dữ liệu hiển thị
        $fav_novels  = $userModel->getFavoriteNovels($profile_id);
        $fav_comics  = $userModel->getFavoriteComics($profile_id);
        $history     = $userModel->getProfileHistory($profile_id, 12);
        $comments    = $userModel->getProfileComments($profile_id, 10);

        // 4. Render View với toàn bộ dữ liệu đã chuẩn bị sẵn
        $this->render('profile/index', [
            'user'       => $user,
            'is_owner'   => $is_owner,
            'msg'        => $msg,
            'msg_type'   => $msg_type,
            'fav_novels' => $fav_novels,
            'fav_comics' => $fav_comics,
            'history'    => $history,
            'comments'   => $comments,
        ]);
    }
}
?>
