<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">✍️ Thêm Chương Mới</h2>
    <a href="index.php?route=admin/novel_chapters&novel_id=<?= $novel_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại Danh sách</a>
</div>

<div id="msg-box" style="display:none;" class="alert"></div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="add-chapter-form">
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label fw-bold">Tiêu đề chương <span class="text-danger">*</span></label>
                    <input type="text" id="ctitle" class="form-control" placeholder="Vd: Chương 1: Bắt đầu..." required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">STT (Thứ tự) <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" id="corder" class="form-control" value="1" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                <textarea id="ccontent" class="form-control" rows="15" required placeholder="Nhập nội dung truyện vào đây... Dùng thẻ <p> hoặc <br> nếu thích."></textarea>
            </div>

            <button type="submit" class="btn btn-success px-4 btn-lg" id="btn-submit">
                Lưu Chương <i class="fas fa-spinner fa-spin d-none" id="add-spinner"></i>
            </button>
        </form>
    </div>
</div>

<script>
const novelId = <?= $novel_id ?>;

document.getElementById('add-chapter-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (novelId === 0) return alert('Lỗi: Thiếu ID Truyện');

    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('add-spinner');
    let msgBox = document.getElementById('msg-box');
    
    btn.disabled = true; spinner.classList.remove('d-none'); msgBox.style.display = 'none';
    
    let res = await API.post('api/admin.php', {
        action: 'add_chapter',
        novel_id: novelId,
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