

<link rel="stylesheet" href="assets/css/custom.css">

<div class="container mt-4 mb-5">
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Truyện Tranh</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
        <h3 class="text-primary fw-bold m-0"><i class="fas fa-images"></i> Danh Sách Truyện Tranh</h3>
    </div>

    <div class="row g-3" id="comics-container">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>

    <nav aria-label="Page navigation" class="mt-4" id="pagination-container" style="display: none;">
        <ul class="pagination justify-content-center" id="pagination-list">
        </ul>
    </nav>

</div>

<script>
let currentPage = new URLSearchParams(window.location.search).get('page') || 1;
currentPage = parseInt(currentPage);

document.addEventListener('DOMContentLoaded', () => {
    loadListComics(currentPage);
});

async function loadListComics(page) {
    let res = await API.get(`api/comics.php?action=list&page=${page}`);
    let container = document.getElementById('comics-container');
    let pagBox = document.getElementById('pagination-container');
    let pagList = document.getElementById('pagination-list');
    
    if (res && res.status === 'success' && res.data && res.data.data.items) {
        let comics = res.data.data.items;
        let imgDomain = "https://img.otruyenapi.com/uploads/comics/";
        
        if (comics.length > 0) {
            container.innerHTML = comics.map(comic => {
                let thumb = imgDomain + comic.thumb_url;
                let chapText = (comic.chaptersLatest && comic.chaptersLatest.length > 0) ? 'Chap ' + comic.chaptersLatest[0].chapter_name : '';
                
                return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="book-card">
                        <a href="index.php?route=comic/detail&slug=${comic.slug}" class="book-thumb">
                            <img src="${thumb}" loading="lazy" alt="${comic.name}" onerror="this.src='assets/images/no-image.jpg'">
                            <span class="badge bg-success badge-overlay">Update</span>
                        </a>
                        <div class="book-body">
                            <h3 class="book-title">
                                <a href="index.php?route=comic/detail&slug=${comic.slug}" title="${comic.name}">${comic.name}</a>
                            </h3>
                            <div class="book-info d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="far fa-star"></i> Mới
                                </span> 
                                <span class="${chapText ? 'text-success fw-bold' : ''} small text-truncate" style="max-width: 50%;">
                                    ${chapText}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
            
            // Build Pagination
            let pagination = res.data.data.params.pagination;
            if (pagination) {
                let totalItems = pagination.totalItems;
                let itemsPerPage = pagination.totalItemsPerPage;
                let totalPages = Math.ceil(totalItems / itemsPerPage);
                
                if (totalPages > 1) {
                    let pHTML = '';
                    let range = 2;
                    pHTML += `<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link" href="?route=comic/list&page=${page - 1}"><i class="fas fa-chevron-left"></i></a></li>`;
                    
                    for (let i = 1; i <= totalPages; i++) {
                        if (i == 1 || i == totalPages || (i >= page - range && i <= page + range)) {
                            pHTML += `<li class="page-item ${i == page ? 'active' : ''}"><a class="page-link" href="?route=comic/list&page=${i}">${i}</a></li>`;
                        } else if (i == page - range - 1 || i == page + range + 1) {
                            pHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                    }
                    
                    pHTML += `<li class="page-item ${page >= totalPages ? 'disabled' : ''}"><a class="page-link" href="?route=comic/list&page=${page + 1}"><i class="fas fa-chevron-right"></i></a></li>`;
                    
                    pagList.innerHTML = pHTML;
                    pagBox.style.display = 'block';
                }
            }
        } else {
            container.innerHTML = `<div class="col-12 text-center py-5"><div class="alert alert-warning">Danh sách trống.</div></div>`;
            pagBox.style.display = 'none';
        }
    } else {
        container.innerHTML = `<div class="col-12 text-center py-5"><div class="alert alert-danger">Lỗi tải dữ liệu truyện tranh.</div></div>`;
        pagBox.style.display = 'none';
    }
}
</script>
