<?php
// File: app/controllers/ChatController.php
require_once 'core/Controller.php';

class ChatController extends Controller
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Yêu cầu đăng nhập mới được xài chat
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('index.php?route=home/index');
        }
    }

    public function index()
    {
        // Giao diện chính của chat. 
        // JavaScript sẽ gọi AJAX sang api/chat.php để lấy danh sách người dùng và tin nhắn.
        $this->render('chat/index', [
            'page_title' => 'Tin Nhắn Cá Nhân'
        ], true);
    }
}
