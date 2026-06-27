

<link rel="stylesheet" href="assets/css/custom.css">

<div class="container mt-4 mb-5">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Truyện Chữ</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
        <h3 class="text-success fw-bold m-0"><i class="fas fa-pen-nib"></i> Kho Truyện Chữ</h3>
        <span class="badge bg-success" id="total-badge">Tổng: 0</span>
    </div>

    <div class="row g-3" id="novels-container">
        <!-- JS render -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-success" role="status"></div>
        </div>
    </div>

    <nav aria-label="Page navigation" class="mt-4" id="pagination-container" style="display: none;">
        <ul class="pagination pagination-sm justify-content-center" id="pagination-list">
        </ul>
    </nav>

</div>

<script>
let currentPage = new URLSearchParams(window.location.search).get('page') || 1;
currentPage = parseInt(currentPage);

document.addEventListener('DOMContentLoaded', () => {
    loadListNovels(currentPage);
});

async function loadListNovels(page) {
    let res = await API.get(`api/novels.php?action=get_list&page=${page}&limit=24`);
    let container = document.getElementById('novels-container');
    let totalBadge = document.getElementById('total-badge');
    let pagBox = document.getElementById('pagination-container');
    let pagList = document.getElementById('pagination-list');
    
    if (res && res.status === 'success') {
        totalBadge.innerText = `Tổng: ${res.pagination.total_records}`;
        
        if (res.data.length > 0) {
            container.innerHTML = res.data.map(novel => {
                let cover = novel.cover_image ? novel.cover_image : 'assets/images/no-image.jpg';
                let is_full = (novel.status === 'completed');
                
                return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="book-card">
                        <a href="index.php?route=novel/detail&id=${novel.id}" class="book-thumb">
                            <img src="${cover}" alt="${novel.title}" loading="lazy">
                            <span class="badge ${is_full ? 'bg-success' : 'bg-info'} badge-overlay">${is_full ? 'Full' : 'On-going'}</span>
                        </a>
                        <div class="book-body">
                            <h3 class="book-title">
                                <a href="index.php?route=novel/detail&id=${novel.id}" title="${novel.title}">${novel.title}</a>
                            </h3>
                            <div class="book-info">
                                <span class="text-truncate w-100">
                                    <i class="fas fa-user-edit"></i> ${novel.author}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
            
            // Build pagination
            if (res.pagination.total_pages > 1) {
                let pHTML = '';
                let totalP = res.pagination.total_pages;
                let curP = res.pagination.page;
                
                pHTML += `<li class="page-item ${curP <= 1 ? 'disabled' : ''}"><a class="page-link" href="?route=novel/list&page=${curP - 1}"><i class="fas fa-chevron-left"></i></a></li>`;
                
                let range = 2;
                for (let i = 1; i <= totalP; i++) {
                    if (i == 1 || i == totalP || (i >= curP - range && i <= curP + range)) {
                        pHTML += `<li class="page-item ${i == curP ? 'active' : ''}"><a class="page-link" href="?route=novel/list&page=${i}">${i}</a></li>`;
                    } else if (i == curP - range - 1 || i == curP + range + 1) {
                        pHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }
                
                pHTML += `<li class="page-item ${curP >= totalP ? 'disabled' : ''}"><a class="page-link" href="?route=novel/list&page=${curP + 1}"><i class="fas fa-chevron-right"></i></a></li>`;
                
                pagList.innerHTML = pHTML;
                pagBox.style.display = 'block';
            } else {
                pagBox.style.display = 'none';
            }
        } else {
            container.innerHTML = `
            <div class="col-12 text-center py-5">
                <p class="text-muted">Chưa có truyện nào trong thư viện.</p>
                <a href="index.php" class="btn btn-outline-primary">Quay lại trang chủ</a>
            </div>`;
            pagBox.style.display = 'none';
        }
    } else {
        container.innerHTML = `<div class="col-12 text-center text-danger py-4">Lỗi kết nối hoặc không có dữ liệu.</div>`;
    }
}
</script>
