<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white fw-bold d-flex align-items-center">
        <i class="fas fa-crown text-warning me-2"></i> BẢNG XẾP HẠNG
    </div>
    <div class="card-body p-0">
        
        <ul class="nav nav-pills nav-fill p-2 bg-light" role="tablist">
            <li class="nav-item">
                <button class="nav-link active py-1 fw-bold" data-bs-toggle="pill" data-bs-target="#top-comic" style="font-size: 14px;">Truyện Tranh</button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-1 fw-bold" data-bs-toggle="pill" data-bs-target="#top-novel" style="font-size: 14px;">Truyện Chữ</button>
            </li>
        </ul>

        <div class="tab-content p-2">
            
            <div class="tab-pane fade show active" id="top-comic">
                <div class="text-center py-4"><span class="spinner-border text-danger spinner-border-sm"></span></div>
            </div>

            <div class="tab-pane fade" id="top-novel">
                <div class="text-center py-4"><span class="spinner-border text-danger spinner-border-sm"></span></div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadTopViews();
});

async function loadTopViews() {
    // Top Truyện Tranh
    let resComic = await API.get('api/sys.php?action=get_top_views&type=comic');
    let containerComic = document.getElementById('top-comic');
    if (resComic && resComic.status === 'success' && resComic.data.length > 0) {
        let htmlComic = '';
        resComic.data.forEach((row, index) => {
            let rank = index + 1;
            let displayView = Number(row.view_count).toLocaleString('vi-VN');
            let comicLink = `index.php?route=comic/detail&slug=${row.slug}`;
            htmlComic += `
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom position-relative item-rank">
                    <div class="rank-number rank-${rank}">${rank}</div>
                    
                    <a href="${comicLink}" class="me-3 position-relative">
                        <img src="${row.thumb_url}" class="rounded shadow-sm border" style="width: 50px; height: 70px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/50x70?text=No+Img'">
                    </a>
                    
                    <div style="flex: 1; min-width: 0;">
                        <h6 class="mb-1 text-truncate">
                            <a href="${comicLink}" class="text-dark text-decoration-none fw-bold" title="${row.name}">
                                ${row.name}
                            </a>
                        </h6>
                        <small class="text-muted" style="font-size: 12px;">
                            <i class="fas fa-eye text-secondary"></i> ${displayView}
                        </small>
                    </div>
                </div>
            `;
        });
        containerComic.innerHTML = htmlComic;
    } else {
        containerComic.innerHTML = '<p class="text-center text-muted py-3">Chưa có dữ liệu xếp hạng.</p>';
    }

    // Top Truyện Chữ
    let resNovel = await API.get('api/sys.php?action=get_top_views&type=novel');
    let containerNovel = document.getElementById('top-novel');
    if (resNovel && resNovel.status === 'success' && resNovel.data.length > 0) {
        let htmlNovel = '';
        resNovel.data.forEach((row, index) => {
            let rank = index + 1;
            let displayView = Number(row.views).toLocaleString('vi-VN');
            let novelLink = `index.php?route=novel/detail&id=${row.id}`;
            htmlNovel += `
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom position-relative item-rank">
                    <div class="rank-number rank-${rank}">${rank}</div>
                    
                    <a href="${novelLink}" class="me-3">
                        <img src="${row.cover_image}" class="rounded shadow-sm border" style="width: 50px; height: 70px; object-fit: cover;" onerror="this.src='assets/images/no-image.jpg'">
                    </a>
                    
                    <div style="flex: 1; min-width: 0;">
                        <h6 class="mb-1 text-truncate">
                            <a href="${novelLink}" class="text-dark text-decoration-none fw-bold" title="${row.title}">
                                ${row.title}
                            </a>
                        </h6>
                        <small class="text-muted" style="font-size: 12px;">
                            <i class="fas fa-eye text-secondary"></i> ${displayView}
                        </small>
                    </div>
                </div>
            `;
        });
        containerNovel.innerHTML = htmlNovel;
    } else {
        containerNovel.innerHTML = '<p class="text-center text-muted py-3">Chưa có dữ liệu xếp hạng.</p>';
    }
}
</script>