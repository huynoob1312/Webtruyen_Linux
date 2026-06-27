<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">✏️ Chỉnh sửa Truyện</h2>
</div>

<div id="msg-box" style="display:none;" class="alert"></div>

<div class="card shadow-sm border-0" id="form-container" style="display: none;">
    <div class="card-body">
        <form id="edit-novel-form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tên truyện <span class="text-danger">*</span></label>
                    <input type="text" id="ntitle" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tác giả <span class="text-danger">*</span></label>
                    <input type="text" id="nauthor" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Thể loại</label>
                <div class="border p-3 rounded bg-light d-flex flex-wrap gap-3" id="cat-checkboxes">
                    <!-- Checkboxes fetched via JS -->
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Link Ảnh bìa</label>
                    <input type="text" id="nimage" class="form-control" placeholder="https://...">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select id="nstatus" class="form-select">
                        <option value="ongoing">Đang tiến hành</option>
                        <option value="completed">Đã hoàn thành</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả / Giới thiệu</label>
                <textarea id="ndesc" class="form-control" rows="5"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4" id="btn-submit">Lưu Thay Đổi <i class="fas fa-spinner fa-spin d-none" id="edit-spinner"></i></button>
                <a href="index.php?route=admin/novels" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<script>
let novelId = new URLSearchParams(window.location.search).get('id');

document.addEventListener('DOMContentLoaded', () => {
    if(!novelId) {
        document.getElementById('msg-box').innerHTML = "Không tìm thấy truyện!";
        document.getElementById('msg-box').className = "alert alert-danger";
        document.getElementById('msg-box').style.display = 'block';
        return;
    }
    loadData();
});

async function loadData() {
    // Load categories first
    let catRes = await API.get('api/admin.php?action=get_categories');
    let cats = [];
    if (catRes && catRes.status === 'success') {
        cats = catRes.data;
    }

    // Load novel
    let res = await API.get('api/admin.php?action=get_novel&id='+novelId);
    if (res && res.status === 'success') {
        let n = res.data;
        document.getElementById('ntitle').value = n.title;
        document.getElementById('nauthor').value = n.author;
        document.getElementById('nimage').value = n.cover_image;
        document.getElementById('nstatus').value = n.status;
        document.getElementById('ndesc').value = n.description;

        // Render check boxes
        let html = '';
        cats.forEach(c => {
            let isChecked = n.categories.includes(String(c.id)) ? 'checked' : '';
            html += `
            <div class="form-check">
                <input class="form-check-input cat-cb" type="checkbox" value="${c.id}" id="cat_${c.id}" ${isChecked}>
                <label class="form-check-label" for="cat_${c.id}">${c.name}</label>
            </div>`;
        });
        document.getElementById('cat-checkboxes').innerHTML = html || 'Không có dữ liệu';
        document.getElementById('form-container').style.display = 'block';
    } else {
        document.getElementById('msg-box').innerHTML = "Lỗi tải dữ liệu truyện!";
        document.getElementById('msg-box').className = "alert alert-danger";
        document.getElementById('msg-box').style.display = 'block';
    }
}

document.getElementById('edit-novel-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('edit-spinner');
    let msgBox = document.getElementById('msg-box');
    
    let checkedCats = [];
    document.querySelectorAll('.cat-cb:checked').forEach(cb => checkedCats.push(cb.value));

    btn.disabled = true; spinner.classList.remove('d-none');
    
    let res = await API.post('api/admin.php', {
        action: 'update_novel',
        id: novelId,
        title: document.getElementById('ntitle').value,
        author: document.getElementById('nauthor').value,
        description: document.getElementById('ndesc').value,
        cover_image: document.getElementById('nimage').value,
        status: document.getElementById('nstatus').value,
        categories: JSON.stringify(checkedCats)
    });
    
    btn.disabled = false; spinner.classList.add('d-none');
    
    if (res && res.status === 'success') {
        msgBox.innerHTML = res.message;
        msgBox.className = 'alert alert-success';
        msgBox.style.display = 'block';
        window.scrollTo(0,0);
    } else {
        msgBox.innerHTML = res ? res.message : 'Lỗi hệ thống';
        msgBox.className = 'alert alert-danger';
        msgBox.style.display = 'block';
        window.scrollTo(0,0);
    }
});
</script>