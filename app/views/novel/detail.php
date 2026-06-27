

<link rel="stylesheet" href="assets/css/detail.css">

<div class="container mt-4" id="detail-skeleton">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
</div>

<div class="container mt-4" id="detail-content" style="display: none;">
    <div class="row">
        <div class="col-md-3 text-center mb-4">
            <div class="card shadow-sm border-0">
                <img id="novel-cover" src="" class="card-img-top rounded card-img-detail" alt="Cover">
            </div>
        </div>

        <div class="col-md-9">
            <h2 class="fw-bold text-primary mb-3" id="novel-title">...</h2>
            
            <div class="mb-3">
                <span class="badge bg-secondary p-2 me-1"><i class="fas fa-pen-nib"></i> <span id="novel-author"></span></span>
                <span class="badge p-2 me-1" id="novel-status"></span>
                <span class="badge bg-info text-dark p-2 me-1"><i class="fas fa-eye"></i> <span id="novel-views">0</span> view</span>
                <span class="badge bg-danger p-2"><i class="fas fa-heart"></i> <span id="fav-count">...</span> thích</span>
            </div>

            <div class="mb-4 d-flex gap-2 flex-wrap" id="read-buttons">
                <!-- Fetch buttons -->
            </div>
            
            <div class="card bg-light border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold border-bottom pb-2 text-dark"><i class="fas fa-info-circle text-warning"></i> Giới thiệu</h5>
                    <div class="card-text text-secondary desc-box-full" id="novel-desc">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-5 shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol text-primary"></i> Danh Sách Chương</h5>
        </div>
        <div class="card-body chapter-list-scroll">
            <div class="row g-2" id="chapter-list">
            </div>
        </div>
    </div>

    <div class="mt-4">
        <?php
        // Section comment vẫn load bằng PHP do liên quan cấu trúc form, 
        // nhưng sau đó sẽ tự động gửi ajax_comment qua form
        $cmt_type = 'novel';
        $cmt_obj_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (file_exists('app/views/includes/comment_section.php')) {
            include 'app/views/includes/comment_section.php';
        }
        ?>
    </div>
</div>

<script>
let novelId = new URLSearchParams(window.location.search).get('id');

document.addEventListener('DOMContentLoaded', () => {
    if(!novelId) {
        document.getElementById('detail-skeleton').innerHTML = `<div class="alert alert-danger text-center">ID không hợp lệ</div>`;
        return;
    }
    loadNovelDetail();
});

async function loadNovelDetail() {
    let res = await API.get(`api/novels.php?action=get_detail&id=${novelId}`);
    if (res && res.status === 'success' && res.data) {
        let novel = res.data.novel;
        let chapters = res.data.chapters;

        document.getElementById('detail-skeleton').style.display = 'none';
        document.getElementById('detail-content').style.display = 'block';

        document.getElementById('novel-title').innerText = novel.title;
        document.getElementById('novel-cover').src = novel.cover_image || 'assets/images/no-image.jpg';
        document.getElementById('novel-author').innerText = novel.author;
        
        let stBadge = document.getElementById('novel-status');
        stBadge.innerText = novel.status === 'completed' ? 'Đã hoàn thành' : 'Đang tiến hành';
        stBadge.classList.add(novel.status === 'completed' ? 'bg-success' : 'bg-warning');
        
        document.getElementById('novel-views').innerText = novel.views.toLocaleString();
        document.getElementById('fav-count').innerText = novel.favorite_count || 0;
        
        // Decode HTML entities for description if needed, or set innerHTML properly
        let descDiv = document.createElement("div");
        descDiv.innerHTML = novel.description;
        document.getElementById('novel-desc').innerHTML = descDiv.innerText || novel.description;

        // Build buttons
        let btnHtml = '';
        if (chapters.length > 0) {
            let firstChap = chapters[chapters.length - 1]; // Because ordered by ID DESC from API
            let latestChap = chapters[0];

            btnHtml += `<a href="index.php?route=novel/read&id=${firstChap.id}" class="btn btn-primary btn-lg px-4 shadow-sm"><i class="fas fa-book-open"></i> Đọc Từ Đầu (${firstChap.title})</a>`;
            btnHtml += `<a href="index.php?route=novel/read&id=${latestChap.id}" class="btn btn-danger btn-lg px-4 shadow-sm"><i class="fas fa-bolt"></i> Mới Nhất (${latestChap.title})</a>`;
        } else {
            btnHtml += `<button class="btn btn-secondary btn-lg px-4" disabled>Chưa có chương</button>`;
        }

        <?php if(isset($_SESSION['user_id'])): ?>
            let isFav = res.data.is_favorited;
            if(isFav) {
                btnHtml += `<button id="btn-favorite" class="btn btn-lg btn-warning text-white shadow-sm" onclick="toggleFavorite()"><i class="fas fa-heart"></i> Đã Theo Dõi</button>`;
            } else {
                btnHtml += `<button id="btn-favorite" class="btn btn-lg btn-outline-warning text-dark shadow-sm" onclick="toggleFavorite()"><i class="far fa-heart"></i> Theo Dõi</button>`;
            }
        <?php else: ?>
            btnHtml += `<a href="index.php?route=auth/login" class="btn btn-outline-warning text-dark btn-lg shadow-sm"><i class="far fa-heart"></i> Đăng nhập để lưu</a>`;
        <?php endif; ?>

        document.getElementById('read-buttons').innerHTML = btnHtml;

        // Build Chapters
        if(chapters.length > 0) {
            // Reverse to ASC order for display
            let ascChaps = [...chapters].reverse();
            document.getElementById('chapter-list').innerHTML = ascChaps.map(c => `
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="index.php?route=novel/read&id=${c.id}" class="text-decoration-none text-dark d-block p-2 border rounded hover-chap novel-chap-item text-truncate" title="${c.title}">${c.title}</a>
                </div>
            `).join('');
        } else {
            document.getElementById('chapter-list').innerHTML = '<div class="text-center py-4 text-muted w-100">Truyện đang cập nhật...</div>';
        }

    } else {
        document.getElementById('detail-skeleton').innerHTML = `<div class="alert alert-danger text-center">Lỗi tải dữ liệu hoặc truyện không tồn tại.</div>`;
    }
}

async function toggleFavorite() {
    // Để có UX tốt hơn, ta gửi yêu cầu lên `api/user.php?action=toggle_favorite`
    let res = await API.post('api/user.php', { action: 'toggle_favorite', type: 'novel', id: novelId });
    if(res) {
        if(res.status === 'added' || res.status === 'removed') {
            loadNovelDetail(); // Reload tạm để update state
        } else {
            alert(res.message);
        }
    }
}
</script>
