<div class="container mt-4">
    <div class="row">
        
        <div class="col-lg-8">
            <!-- Khu vực danh sách truyện tranh -->
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h4 class="text-primary fw-bold m-0"><i class="fas fa-images"></i> Truyện Tranh</h4>
                <a href="index.php?route=comic/list" class="btn btn-sm btn-outline-primary">Xem thêm <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="row g-3 mb-5" id="home-comics">
                <div class="col-12 text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>

            <!-- Khu vực danh sách truyện chữ -->
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h4 class="text-success fw-bold m-0"><i class="fas fa-pen-nib"></i> Truyện Chữ</h4>
                <a href="index.php?route=novel/list" class="btn btn-sm btn-outline-success">Xem thêm <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="row g-3" id="home-novels">
                <div class="col-12 text-center py-4">
                    <div class="spinner-border text-success" role="status"></div>
                </div>
            </div>
        </div> 
        
        <div class="col-lg-4">
            <!-- Đọc tiếp (Lịch sử) - Chỉ hiện khi đăng nhập -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div id="home-history-container" style="display: none;">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-history"></i> Đọc tiếp
                        </div>
                        <ul class="list-group list-group-flush" id="home-history-list">
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
                if (file_exists('app/views/includes/top_view.php')) {
                    include 'app/views/includes/top_view.php';
                }
            ?>

            <div class="card shadow-sm mt-4 mb-4 border-0">
                <div class="card-body text-center">
                    <h5 class="fw-bold text-primary">Cộng đồng</h5>
                    <p class="text-muted small mb-3">Tham gia để bàn luận về truyện!</p>
                    <a href="https://www.facebook.com/utt.edu.vn" class="btn btn-outline-primary btn-sm w-100 mb-2"><i class="fab fa-facebook"></i> Facebook</a>
                    <a href="https://discord.gg/zHDZhC5Qp3" class="btn btn-outline-dark btn-sm w-100"><i class="fab fa-discord"></i> Discord</a>
                </div>
            </div>
        </div> 
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    loadHomeNovels();
});


async function loadHomeNovels() {
    let res = await API.get('api/novels.php?action=get_home_novels');
    let container = document.getElementById('home-novels');
    
    if (res && res.status === 'success' && res.data.length > 0) {
        let html = res.data.map(novel => {
            let cover = novel.cover_image ? novel.cover_image : 'assets/images/no-image.jpg';
            let label = (novel.status === 'completed') ? 'Full' : 'New';
            let color = (novel.status === 'completed') ? 'bg-success' : 'bg-info';
            
            let timeSource = novel.latest_chap_date || novel.updated_at || new Date().toISOString();
            let timeStr = timeAgo(timeSource);
            let chapName = novel.latest_chap_title ? novel.latest_chap_title : novel.author;

            return `
            <div class="col-4 col-md-3">
                <div class="book-card">
                    <a href="index.php?route=novel/detail&id=${novel.id}" class="book-thumb">
                        <img src="${cover}" alt="${novel.title}" loading="lazy">
                        <span class="badge ${color} badge-overlay">${label}</span>
                    </a>
                    <div class="book-body">
                        <h3 class="book-title">
                            <a href="index.php?route=novel/detail&id=${novel.id}" title="${novel.title}">${novel.title}</a>
                        </h3>
                        <div class="book-info d-flex justify-content-between align-items-center">
                            <span class="${novel.latest_chap_title ? 'text-success fw-bold' : 'text-muted'} small text-truncate" style="max-width: 65%;">
                                ${novel.latest_chap_title ? chapName : '<i class="fas fa-user-edit"></i> ' + novel.author}
                            </span>
                            <span class="text-muted small" style="font-size: 0.8rem;"><i class="far fa-clock"></i> ${timeStr}</span>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');
        container.innerHTML = html;
    } else {
        container.innerHTML = `<div class="col-12 text-center text-muted py-4">Chưa có truyện chữ nào.</div>`;
    }
}

</script>
