<?php
// File: app/controllers/AdminController.php
require_once 'core/Controller.php';

class AdminController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        // Kiểm tra quyền (chỉ cho admin & mod)
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'mod')) {
            header("Location: index.php");
            exit;
        }
    }

    // Ghi đè phương thức render để sử dụng admin layout thay vì main layout
    protected function render($view, $data = [], $layout = true) {
        $viewFile = 'app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // Tự động đẩy $_GET params vào $data để các view có thể dùng $id, $novel_id
            $data = array_merge($_GET, $data);
            
            // Giải nén data để view có thể dùng
            extract($data);
            
            // Nếu dùng layout
            if ($layout) {
                ob_start();
                require $viewFile;
                $page_content = ob_get_clean();
                
                // Mặc định tên trang
                if (!isset($page_title)) $page_title = 'Admin Panel';
                if (!isset($active_menu)) $active_menu = 'dashboard';
                
                require 'app/views/layouts/admin.php';
            } else {
                require $viewFile;
            }
        } else {
            die("View '$viewFile' không tồn tại!");
        }
    }

    public function dashboard() {
        $this->render('admin/dashboard', [
            'page_title' => 'Tổng Quan',
            'active_menu' => 'dashboard'
        ]);
    }

    public function novels() {
        $this->render('admin/novels', [
            'page_title' => 'Quản lý Truyện',
            'active_menu' => 'novels'
        ]);
    }

    public function novel_add() {
        $this->render('admin/novel_add', [
            'page_title' => 'Thêm Truyện',
            'active_menu' => 'novels'
        ]);
    }

    public function novel_edit() {
        $id = $_GET['id'] ?? 0;
        $this->render('admin/novel_edit', [
            'page_title' => 'Sửa Truyện',
            'active_menu' => 'novels',
            'id' => $id
        ]);
    }

    public function novel_chapters() {
        $novel_id = $_GET['novel_id'] ?? $_GET['id'] ?? 0;
        if (!$novel_id) {
            die("Thiếu ID truyện");
        }
        $this->render('admin/novel_chapters', [
            'page_title' => 'Quản lý Chương',
            'active_menu' => 'novels',
            'novel_id' => $novel_id
        ]);
    }

    public function chapter_add() {
        $novel_id = $_GET['novel_id'] ?? $_GET['id'] ?? 0;
        $this->render('admin/chapter_add', [
            'page_title' => 'Thêm Chương',
            'active_menu' => 'novels',
            'novel_id' => $novel_id
        ]);
    }

    public function chapter_edit() {
        $id = $_GET['id'] ?? 0;
        $this->render('admin/chapter_edit', [
            'page_title' => 'Sửa Chương',
            'active_menu' => 'novels',
            'id' => $id,
            'novel_id' => $_GET['novel_id'] ?? 0
        ]);
    }

    public function categories() {
        $this->render('admin/categories', [
            'page_title' => 'Quản lý Thể loại',
            'active_menu' => 'categories'
        ]);
    }

    public function users() {
        // Chỉ admin mới được quản lý users
        if ($_SESSION['role'] !== 'admin') {
            header("Location: index.php?route=admin/dashboard");
            exit;
        }
        $this->render('admin/users', [
            'page_title' => 'Quản lý Thành viên',
            'active_menu' => 'users'
        ]);
    }

    public function comments() {
        // Chỉ admin mới được quản lý bình luận
        if ($_SESSION['role'] !== 'admin') {
            header("Location: index.php?route=admin/dashboard");
            exit;
        }
        $this->render('admin/comments', [
            'page_title' => 'Quản lý Bình luận',
            'active_menu' => 'comments'
        ]);
    }

    public function notifications() {
        // Chỉ admin mới được gửi thông báo
        if ($_SESSION['role'] !== 'admin') {
            header("Location: index.php?route=admin/dashboard");
            exit;
        }
        $this->render('admin/notifications', [
            'page_title' => 'Gửi thông báo',
            'active_menu' => 'notifications'
        ]);
    }

    public function forum() {
        require_once 'app/models/ForumModel.php';
        $forumModel = new ForumModel();
        $topics = $forumModel->getAllTopicsForAdmin();

        $this->render('admin/forum', [
            'page_title' => 'Quản lý Diễn đàn',
            'active_menu' => 'forum',
            'topics'     => $topics
        ]);
    }
}
?>