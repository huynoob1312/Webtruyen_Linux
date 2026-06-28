

<link rel="stylesheet" href="assets/css/read.css">

<div class="read-wrapper" id="content-skeleton">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
</div>

<div class="read-wrapper" id="content-body" style="display: none;">
    <div class="read-container">
        
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1" id="chap-name-display">...</h4>
            <p class="text-muted small">
                Truyện: <a href="#" id="comic-link-display" class="text-primary text-decoration-none fw-bold">...</a>
            </p>
        </div>

        <div class="chapter-nav">
            <a href="#" class="btn btn-secondary btn-nav disabled" id="btn-prev">⬅ Trước</a>
            
            <select class="form-select chapter-select" id="chapter-select-box" onchange="location = this.value;">
                <option value="">Đang tải...</option>
            </select>

            <a href="#" class="btn btn-primary btn-nav disabled" id="btn-next">Sau ➡</a>
        </div>

        <div class="comic-content my-4" id="comic-images-container">
            <!-- Images will be injected here -->
        </div>

        <div class="chapter-nav mt-4">
            <a href="#" class="btn btn-secondary btn-nav disabled" id="btn-prev-bottom">⬅ Trước</a>
            <a href="#" class="btn btn-primary btn-nav disabled" id="btn-next-bottom">Sau ➡</a>
        </div>

        <div class="mt-5">
            <?php
                if (!isset($_GET['slug'])) $_GET['slug'] = '';
                $cmt_type = 'comic';
                $cmt_obj_id = $_GET['slug'];
                if (file_exists('app/views/includes/comment_section.php')) {
                    include 'app/views/includes/comment_section.php';
                }
            ?>
        </div>
    </div>
</div>

<script>
let urlParams = new URLSearchParams(window.location.search);
let apiEncoded = urlParams.get('api');
let comicSlug = urlParams.get('slug');
let chapNameRaw = urlParams.get('name') || 'Đọc truyện';

document.addEventListener('DOMContentLoaded', () => {
    if(!apiEncoded || !comicSlug) {
        document.getElementById('content-skeleton').innerHTML = `<div class="alert alert-danger text-center m-5">Lỗi: Thiếu thông tin.</div>`;
        return;
    }
    document.getElementById('chap-name-display').innerText = decodeURIComponent(chapNameRaw);
    loadComicChapter();
});

async function loadComicChapter() {
    // 1. Fetch images (dùng proxy qua backend server để tránh bị chặn IP từ api, 
    // hoặc tải trực tiếp ở FE nếu được, ở đây ta gọi server)
    let resChap = await API.get(`api/comics.php?action=chapter&api=${encodeURIComponent(apiEncoded)}`);
    
    // 2. Fetch context (Danh sách chapters)
    let resStory = await API.get(`api/comics.php?action=detail&slug=${comicSlug}`);

    document.getElementById('content-skeleton').style.display = 'none';
    document.getElementById('content-body').style.display = 'block';

    // Render Images
    let container = document.getElementById('comic-images-container');
    if (resChap && resChap.status === 'success' && resChap.data && resChap.data.data.item) {
        let chapterItem = resChap.data.data.item;
        let cdn = resChap.data.data.domain_cdn;
        let path = chapterItem.chapter_path;
        let images = chapterItem.chapter_image;

        if (images && images.length > 0) {
            container.innerHTML = images.map(img => `
                <div class="text-center">
                    <img src="${cdn}/${path}/${img.image_file}" class="comic-image-item" loading="lazy" alt="Page">
                </div>
            `).join('');
        } else {
            container.innerHTML = `<div class="alert alert-warning text-center">Chương này chưa có ảnh.</div>`;
        }
    } else {
        container.innerHTML = `<div class="alert alert-danger text-center m-5">Lỗi tải ảnh API.</div>`;
    }

    // Render Nav
    if (resStory && resStory.status === 'success' && resStory.data) {
        let story = resStory.data.data.item;
        let chapters = [];
        if (story.chapters && story.chapters.length > 0) {
            chapters = story.chapters[0].server_data;
        }

        document.getElementById('comic-link-display').innerText = story.name;
        document.getElementById('comic-link-display').href = `index.php?route=comic/detail&slug=${comicSlug}`;

        // Lưu lịch sử (History)
        saveHistory(story, false);

        if(chapters.length > 0) {
            let selectBox = document.getElementById('chapter-select-box');
            selectBox.innerHTML = '';

            let foundIndex = -1;
            let currentApiUrl = atob(apiEncoded);

            for (let i = 0; i < chapters.length; i++) {
                if (chapters[i].chapter_api_data === currentApiUrl) {
                    foundIndex = i;
                }
                let oName = encodeURIComponent(story.name + ' - Chap ' + chapters[i].chapter_name);
                let oLink = `index.php?route=comic/read&api=${btoa(chapters[i].chapter_api_data)}&name=${oName}&slug=${story.slug}`;
                let isSel = (chapters[i].chapter_api_data === currentApiUrl) ? 'selected' : '';
                selectBox.innerHTML += `<option value="${oLink}" ${isSel}>Chap ${chapters[i].chapter_name}</option>`;
            }

            if (foundIndex !== -1) {
                let isDesc = false;
                if (chapters.length > 1) {
                    if (parseFloat(chapters[0].chapter_name) > parseFloat(chapters[1].chapter_name)) {
                        isDesc = true;
                    }
                }

                let nextIdx = isDesc ? foundIndex - 1 : foundIndex + 1;
                let prevIdx = isDesc ? foundIndex + 1 : foundIndex - 1;

                if (chapters[nextIdx]) {
                    let nName = encodeURIComponent(story.name + ' - Chap ' + chapters[nextIdx].chapter_name);
                    let nLink = `index.php?route=comic/read&api=${btoa(chapters[nextIdx].chapter_api_data)}&name=${nName}&slug=${story.slug}`;
                    let nBtns = [document.getElementById('btn-next'), document.getElementById('btn-next-bottom')];
                    nBtns.forEach(b => { b.href = nLink; b.classList.remove('disabled'); });
                }

                if (chapters[prevIdx]) {
                    let pName = encodeURIComponent(story.name + ' - Chap ' + chapters[prevIdx].chapter_name);
                    let pLink = `index.php?route=comic/read&api=${btoa(chapters[prevIdx].chapter_api_data)}&name=${pName}&slug=${story.slug}`;
                    let pBtns = [document.getElementById('btn-prev'), document.getElementById('btn-prev-bottom')];
                    pBtns.forEach(b => { b.href = pLink; b.classList.remove('disabled'); });
                }
            }
        }
    }
}

async function saveHistory(story, isDelay = true) {
    <?php if(!isset($_SESSION['user_id'])) echo "return;"; ?>
    
    let formData = new FormData();
    formData.append('action', 'history_add');
    formData.append('type', 'comic');
    formData.append('item_id', story.slug);
    formData.append('item_name', story.name);
    // Use imgDomain as hardcoded because it comes from API wrapper usually...
    formData.append('item_image', `https://img.otruyenapi.com/uploads/comics/${story.thumb_url}`);
    
    // chap name decoded
    let cNameDisplay = document.getElementById('chap-name-display').innerText;
    formData.append('chapter_name', cNameDisplay);
    formData.append('chapter_url', window.location.href);

    // Call user API 
    // Wait, we haven't implemented history_add in user.php yet. 
    // Let's implement it next or the server proxy can handle it!
    // Since Phase 2 includes history, we should send it to user.php api.
    await API.post('api/user.php', Object.fromEntries(formData));
}
</script>
