<?php
// ================================================================
// HEADER.PHP - API DRIVEN VERSION
// ================================================================
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebDocTruyen - Đọc Truyện Online</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">

    <!-- Thêm JS API chung -->
    <script src="assets/js/api.js"></script>

    <style>
        .scrollable-menu { max-height: 400px; overflow-y: auto; }
        .nav-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 8px; border: 1px solid #fff; }
        .btn-trash:hover { color: #dc3545 !important; transform: scale(1.1); transition: 0.2s; }
        .notif-unread { background-color: #f0f8ff; font-weight: 500; }
        .notif-read { background-color: #fff; }
        /* CSS cho kết quả live search render động */
        #live-search-result { position: absolute; top: 100%; left: 0; right: 0; background: #fff; z-index: 1050; border-radius: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); display: none; overflow: hidden; }
        .search-group-title { padding: 8px 12px; background: #f8f9fa; font-size: 0.85rem; font-weight: bold; color: #6c757d; display: flex; align-items: center; gap: 5px; }
        .search-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; transition: 0.2s; }
        .search-item:hover { background: #f8f9fa; }
        .search-item img { width: 40px; height: 50px; object-fit: cover; border-radius: 3px; margin-right: 12px; }
        .search-item .info { flex: 1; min-width: 0; }
        .search-item .name { font-weight: 600; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
        .search-item .meta { font-size: 0.8rem; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning" href="index.php"><i class="fas fa-book-open"></i> WebDocTruyen</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Trang chủ</a></li>
        
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Thể loại Chữ</a>
            <ul class="dropdown-menu scrollable-menu" id="menu-novel-cats">
                <li><span class="dropdown-item text-muted">Đang tải...</span></li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Thể loại Tranh</a>
            <ul class="dropdown-menu scrollable-menu" id="menu-comic-cats">
                <li><span class="dropdown-item text-muted">Đang tải...</span></li>
            </ul>
        </li>
        <li class="nav-item"><a class="nav-link fw-bold text-success" href="index.php?route=forum/index"><i class="fas fa-comments"></i> Diễn Đàn</a></li>
      </ul>
      
      <div class="d-flex position-relative me-3 my-2 my-lg-0" style="min-width: 300px;">
        <form action="index.php" method="GET" class="d-flex w-100" id="search-form" onsubmit="event.preventDefault(); window.location.href='index.php?route=search/index&q=' + document.getElementById('live-search-input').value;">
<input type="hidden" name="route" value="search/index">
            <input id="live-search-input" class="form-control me-2" type="search" name="q" placeholder="Tìm truyện..." autocomplete="off">
            <button class="btn btn-outline-warning" type="submit"><i class="fas fa-search"></i></button>
        </form>
        <div id="live-search-result"></div>
      </div>

      <ul class="navbar-nav align-items-center" id="user-menu-area">
        <!-- Render bằng JS hoặc PHP session. Để tránh chớp chớp giao diện, ta render PHP trước -->
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php 
                $display_avatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username'].'&background=random';
            ?>
            <li class="nav-item me-2">
                <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal" title="Báo lỗi">
                    <i class="fas fa-bug"></i>
                </button>
            </li>

            <li class="nav-item me-2">
                <a class="nav-link position-relative" href="index.php?route=chat/index" title="Tin nhắn">
                    <i class="fas fa-comment-dots text-primary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="chat-global-unread" style="display:none; font-size: 10px;">0</span>
                </a>
            </li>

            <li class="nav-item dropdown me-3">
                <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notif-count" style="display:none; font-size: 10px;">0</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 340px; max-height: 450px; overflow-y: auto;">
                    <li class="p-2 border-bottom fw-bold bg-light text-dark d-flex justify-content-between align-items-center sticky-top" style="z-index: 1000;">
                        <span><i class="fas fa-bell me-1"></i> Thông báo</span>
                        <div>
                            <small class="text-danger me-2" style="cursor: pointer; font-size: 0.85rem;" onclick="deleteAllNotifs()">
                                <i class="fas fa-trash"></i> Xóa hết
                            </small>
                            <small class="text-primary" style="cursor: pointer; font-size: 0.85rem;" onclick="loadNotifications()">
                                <i class="fas fa-sync-alt"></i>
                            </small>
                        </div>
                    </li>
                    <div id="notif-list">
                        <li class="text-center p-3 text-muted small">Đang tải...</li>
                    </div>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                    <img src="<?= htmlspecialchars($display_avatar) ?>" class="nav-avatar">
                    <span class="fw-bold text-light"><?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="index.php?route=profile/index"><i class="fas fa-user-circle me-2"></i> Trang cá nhân</a></li>
                    
                    <?php if($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'mod'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-bold" href="index.php?route=admin/dashboard"><i class="fas fa-cogs me-2"></i> Trang Quản Trị</a></li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-secondary" href="javascript:void(0)" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="index.php?route=auth/login">Đăng nhập</a></li>
            <li class="nav-item">
                <a class="btn btn-warning text-dark px-3 ms-2 fw-bold" href="index.php?route=auth/register">Đăng ký</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Modal Báo lỗi -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Gửi Báo Lỗi / Góp Ý</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" id="report-title" class="form-control" placeholder="Vd: Lỗi chương 5, Ảnh hỏng...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nội dung</label>
                    <textarea id="report-msg" class="form-control" rows="4" placeholder="Mô tả chi tiết..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger" onclick="sendReport()">Gửi ngay</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // 1. Kiểm tra session real-time qua API thay vì vòng load server
        let sessData = await API.get('api/auth.php?action=check_session');
        if (sessData && sessData.status === 'error' && sessData.message === 'banned') {
            alert('Tài khoản của bạn đã bị KHÓA vĩnh viễn!');
            window.location.href = 'index.php?route=auth/login';
        }

        // 2. Load Danh mục (Text + Comic)
        loadCategories();

        // 3. Khởi tạo Live Search
        initLiveSearch();

        // 4. Notifications & Chat Unread
        <?php if(isset($_SESSION['user_id'])): ?>
            loadNotifications();
            loadChatUnread();
            setInterval(() => {
                loadNotifications();
                loadChatUnread();
            }, 15000); // Check mỗi 15s
        <?php endif; ?>
    });

    async function loadCategories() {
        // Truyện chữ
        let novelCats = await API.get('api/sys.php?action=get_categories');
        let novelUl = document.getElementById('menu-novel-cats');
        if (novelCats && novelCats.data && novelCats.data.length > 0) {
            novelUl.innerHTML = novelCats.data.map(c => `<li><a class="dropdown-item" href="index.php?route=category/novel&slug=${c.slug}">${c.name}</a></li>`).join('');
        } else {
            novelUl.innerHTML = `<li><span class="dropdown-item text-muted">Chưa có dữ liệu</span></li>`;
        }

        // Truyện tranh
        let comicCats = await API.get('api/comics.php?action=categories');
        let comicUl = document.getElementById('menu-comic-cats');
        if (comicCats && comicCats.data && comicCats.data.length > 0) {
            comicUl.innerHTML = comicCats.data.map(c => `<li><a class="dropdown-item" href="index.php?route=category/comic&slug=${c.slug}">${c.name}</a></li>`).join('');
        } else {
            comicUl.innerHTML = `<li><span class="dropdown-item text-muted">Lỗi API</span></li>`;
        }
    }

    function initLiveSearch() {
        const searchInput = document.getElementById('live-search-input');
        const resultBox = document.getElementById('live-search-result');
        let timeout = null;
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                let keyword = this.value;
                if (keyword.length === 0) { resultBox.style.display = 'none'; return; }
                clearTimeout(timeout);
                timeout = setTimeout(async function() {
                    let d = await API.post('api/search.php', { keyword: keyword });
                    if(d && d.status === 'success' && d.data.length > 0) {
                        let html = '';
                        d.data.forEach(group => {
                            html += `<div class="search-group-title">${group.title}</div>`;
                            group.items.forEach(item => {
                                let link = group.type === 'novel' ? `index.php?route=novel/detail&id=${item.id}` : `index.php?route=comic/detail&slug=${item.slug}`;
                                let img = group.type === 'novel' ? item.cover_image : item.thumb;
                                let name = group.type === 'novel' ? item.title : item.name;
                                let meta = group.type === 'novel' ? item.author : item.meta;
                                html += `
                                <a href="${link}" class="search-item">
                                    <img src="${img}" onerror="this.src='assets/images/no-image.jpg'">
                                    <div class="info">
                                        <div class="name">${name}</div>
                                        <div class="meta">${meta}</div>
                                    </div>
                                </a>`;
                            });
                        });
                        resultBox.innerHTML = html;
                        resultBox.style.display = 'block';
                    } else {
                        resultBox.innerHTML = '<div class="p-2 text-muted text-center">Không tìm thấy truyện nào...</div>';
                        resultBox.style.display = 'block';
                    }
                }, 300);
            });
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultBox.contains(e.target)) resultBox.style.display = 'none';
            });
        }
    }

    async function sendReport() {
        let title = document.getElementById('report-title').value;
        let msg = document.getElementById('report-msg').value;
        if(!title || !msg) { alert('Vui lòng nhập đủ thông tin!'); return; }

        let res = await API.post('api/user.php', { action: 'notif_report', title: title, message: msg });
        if(res) {
            alert(res.message);
            if(res.status === 'success') {
                document.getElementById('report-title').value = '';
                document.getElementById('report-msg').value = '';
                var myModal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                myModal.hide();
            }
        }
    }

    async function loadNotifications() {
        let res = await API.post('api/user.php', { action: 'notif_get' });
        if(res && res.status === 'success') {
            let countBadge = document.getElementById('notif-count');
            let notifList = document.getElementById('notif-list');
            
            if(res.data.unread > 0) {
                countBadge.innerText = res.data.unread;
                countBadge.style.display = 'block';
            } else {
                countBadge.style.display = 'none';
            }

            let html = '';
            if(res.data.notifications.length === 0) {
                html = '<li class="text-center p-4 text-muted small"><i class="fas fa-bell-slash fa-2x mb-2 text-secondary"></i><br>Không có thông báo mới.</li>';
            } else {
                res.data.notifications.forEach(n => {
                    let bgClass = n.is_read == 0 ? 'notif-unread' : 'notif-read';
                    let iconType = '<i class="fas fa-info-circle text-primary"></i>';
                    if (n.type == 'report') iconType = '<i class="fas fa-exclamation-circle text-danger"></i>';
                    else if (n.type == 'reply') iconType = '<i class="fas fa-reply text-success"></i>';
                    else if (n.type == 'system' && n.sender_id == 0) iconType = '<i class="fas fa-book-open text-warning"></i>';
                    
                    html += `
                        <li class="dropdown-item p-2 border-bottom ${bgClass} position-relative" style="white-space: normal;">
                            <div class="d-flex align-items-start">
                                <img src="${n.sender_avatar}" class="rounded-circle me-2 border" width="40" height="40" style="object-fit:cover; min-width:40px;">
                                <div class="flex-grow-1 pe-2" onclick="markReadAndGo(${n.id}, '${n.target_url || ''}')" style="cursor:pointer">
                                    <div class="small text-dark mb-1 fw-bold">${iconType} ${n.title}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px; font-size: 0.85rem;">${n.message}</div>
                                    <div class="text-secondary mt-1" style="font-size: 10px;"><i class="far fa-clock"></i> ${n.time_ago} • ${n.sender_name}</div>
                                </div>
                                <button class="btn btn-sm text-secondary btn-trash p-0 ms-1" onclick="deleteNotif(${n.id}, event)" title="Xóa thông báo này">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </li>
                    `;
                });
            }
            notifList.innerHTML = html;
        }
    }

    async function markReadAndGo(id, target_url) {
        await API.post('api/user.php', { action: 'notif_mark_read', id: id });
        if(target_url && target_url !== 'null' && target_url.length > 0) {
            window.location.href = target_url;
        } else {
            loadNotifications();
        }
    }

    async function deleteNotif(id, event) {
        if(event) event.stopPropagation(); 
        if(!confirm('Bạn muốn xóa thông báo này?')) return;
        
        await API.post('api/user.php', { action: 'notif_delete', id: id });
        loadNotifications();
    }

    async function deleteAllNotifs() {
        if(!confirm('CẢNH BÁO: Bạn có chắc muốn xóa TOÀN BỘ thông báo không?')) return;
        let res = await API.post('api/user.php', { action: 'notif_delete_all' });
        if(res){
            alert(res.message);
            loadNotifications();
        }
    }

    async function logout() {
        await API.post('api/auth.php', { action: 'logout' });
        window.location.href = 'index.php';
    }
    async function loadChatUnread() {
        try {
            let res = await API.get('api/chat.php?action=get_unread_total');
            if(res && res.status === 'success') {
                let badge = document.getElementById('chat-global-unread');
                if(badge) {
                    if(res.unread_total > 0) {
                        badge.innerText = res.unread_total;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        } catch(e) {}
    }
</script>