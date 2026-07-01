<?php
// File: app/views/includes/auth.php
// Helper để kiểm tra đăng nhập, sử dụng UserModel thay vì SQL thô
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?route=auth/login");
        exit;
    }

    // Kiểm tra trạng thái account từ DB (chống session cũ sau khi bị ban)
    require_once __DIR__ . '/../../models/UserModel.php';
    $userModel = new UserModel();
    $dbUser = $userModel->checkSessionStatus($_SESSION['user_id']);

    if (!$dbUser) {
        // User đã bị xóa khỏi DB
        session_unset();
        session_destroy();
        header("Location: index.php?route=auth/login");
        exit;
    }

    if ($dbUser['status'] === 'banned') {
        session_unset();
        session_destroy();
        die("<div class='container mt-5'><div class='alert alert-danger text-center'><h4>🚫 Tài khoản bị khóa!</h4><p>Tài khoản của bạn đã bị vô hiệu hóa do vi phạm quy định.</p><a href='index.php' class='btn btn-primary'>Về trang chủ</a></div></div>");
    }
}
