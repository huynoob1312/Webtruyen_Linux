<?php
// File: app/views/layouts/admin.php
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?? 'Admin Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <script src="assets/js/api.js"></script> <!-- Chèn bộ API xịn xò -->
    
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: #cfd8dc; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #495057; border-radius: 5px; }
        .card-stat { transition: 0.3s; }
        .card-stat:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 3rem; position: absolute; right: 20px; top: 20px; opacity: 0.2; }
        .bg-gradient-1 { background: linear-gradient(45deg, #4e54c8, #8f94fb); color: white; }
        .bg-gradient-2 { background: linear-gradient(45deg, #11998e, #38ef7d); color: white; }
        .bg-gradient-3 { background: linear-gradient(45deg, #f2994a, #f2c94c); color: white; }
        .bg-gradient-4 { background: linear-gradient(45deg, #ff4b2b, #ff416c); color: white; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Nơi chứa Sidebar Menu -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none border-bottom pb-3">
            <span class="fs-4 fw-bold"><i class="fas fa-user-shield me-2"></i> <span id="panel-title"><?= ($_SESSION['role'] ?? '') === 'admin' ? 'Admin Panel' : 'Mod Panel' ?></span></span>
        </a>
        
        <ul class="nav nav-pills flex-column mb-auto mt-3" id="admin-menu">
            <li><a href="index.php?route=admin/dashboard" class="nav-link mb-2 <?php if($active_menu=='dashboard') echo 'active'; ?>"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="index.php?route=admin/novels" class="nav-link mb-2 <?php if($active_menu=='novels') echo 'active'; ?>"><i class="fas fa-book me-2"></i> Quản lý Truyện</a></li>
            <li><a href="index.php?route=admin/categories" class="nav-link mb-2 <?php if($active_menu=='categories') echo 'active'; ?>"><i class="fas fa-folder me-2"></i> Quản lý Thể loại</a></li>
            <li><a href="index.php?route=admin/forum" class="nav-link mb-2 <?php if($active_menu=='forum') echo 'active'; ?>"><i class="fas fa-comments me-2"></i> Quản lý Diễn đàn</a></li>
            
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li><a href="index.php?route=admin/users" class="nav-link mb-2 <?php if($active_menu=='users') echo 'active'; ?>"><i class="fas fa-users me-2"></i> Quản lý Thành viên</a></li>
                <li><a href="index.php?route=admin/comments" class="nav-link mb-2 <?php if($active_menu=='comments') echo 'active'; ?>"><i class="fas fa-comments me-2"></i> Quản lý Bình luận</a></li>
                <li><a href="index.php?route=admin/notifications" class="nav-link mb-2 <?php if($active_menu=='notifications') echo 'active'; ?>"><i class="fas fa-bullhorn me-2"></i> Gửi Thông Báo</a></li>
            <?php endif; ?>

            <li class="mt-4 border-top pt-3"></li>
            <li><a href="index.php" class="nav-link text-warning"><i class="fas fa-home me-2"></i> Về trang chủ</a></li>
            <li><a href="javascript:void(0)" class="nav-link text-danger" onclick="adminLogout()"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Nơi chứa Context từng Trang -->
    <div class="container-fluid p-4" style="flex:1;">
        <!-- CONTENT TRUYỀN VÀO TỪ FILE GỐC SẼ HIỂN THỊ DƯỚI ĐÂY -->
        <?php echo $page_content; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Check Auth ngầm chạy xuyên suốt hệ thống Admin
document.addEventListener('DOMContentLoaded', async () => {
    // Basic ping request to sys.php or API module if needed, 
    // Nhưng vì Dashboard API sẽ check là đủ r.
});

async function adminLogout() {
    let res = await API.post('api/auth.php', {action: 'logout'});
    window.location.href = 'index.php?route=auth/login';
}
</script>
</body>
</html>
