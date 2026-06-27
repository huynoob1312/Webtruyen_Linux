<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">✏️ Chỉnh Sửa Chương</h2>
    <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại Danh sách</a>
</div>

<div id="msg-box" style="display:none;" class="alert"></div>

<div class="card shadow-sm border-0" id="form-container" style="display: none;">
    <div class="card-body">
        <form id="edit-chapter-form">
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label fw-bold">Tiêu đề chương <span class="text-danger">*</span></label>
                    <input type="text" id="ctitle" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">STT (Thứ tự) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" id="corder" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                <textarea id="ccontent" class="form-control" rows="15" required></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4 btn-lg" id="btn-submit">
                    Lưu Cập Nhật <i class="fas fa-spinner fa-spin d-none" id="edit-spinner"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let chapId = new URLSearchParams(window.location.search).get('id');
let novelId = 0;

document.addEventListener('DOMContentLoaded', () => {
    if(!chapId) {
        document.getElementById('msg-box').innerHTML = "Lỗi: Thiếu ID Chương";
        document.getElementById('msg-box').className = "alert alert-danger";
        document.getElementById('msg-box').style.display = 'block';
        return;
    }
    loadChapterData();
});

async function loadChapterData() {
    let res = await API.get('api/admin.php?action=get_chapter&id=' + chapId);
    if (res && res.status === 'success') {
        document.getElementById('ctitle').value = res.data.title;
        document.getElementById('corder').value = res.data.order_index;
        document.getElementById('ccontent').value = res.data.content;
        novelId = res.data.novel_id;
        document.getElementById('form-container').style.display = 'block';
    } else {
        document.getElementById('msg-box').innerHTML = res ? res.message : "Lỗi server";
        document.getElementById('msg-box').className = "alert alert-danger";
        document.getElementById('msg-box').style.display = 'block';
    }
}

document.getElementById('edit-chapter-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('edit-spinner');
    let msgBox = document.getElementById('msg-box');
    
    btn.disabled = true; spinner.classList.remove('d-none'); msgBox.style.display = 'none';
    
    let res = await API.post('api/admin.php', {
        action: 'update_chapter',
        id: chapId,
        title: document.getElementById('ctitle').value,
        content: document.getElementById('ccontent').value,
        order_index: document.getElementById('corder').value
    });
    
    btn.disabled = false; spinner.classList.add('d-none');
    
    if (res && res.status === 'success') {
        window.location.href = 'index.php?route=admin/novel_chapters&novel_id=' + novelId;
    } else {
        msgBox.innerHTML = res ? res.message : 'Lỗi hệ thống';
        msgBox.className = 'alert alert-danger';
        msgBox.style.display = 'block';
        window.scrollTo(0,0);
    }
});
</script>