<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">📣 Gửi Thông Báo Hệ Thống</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form id="notify-form">
                    <div id="msg-box" class="alert" style="display:none;"></div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gửi đến (Nhập ID User hoặc bỏ trống để Gửi Tất Cả)</label>
                        <input type="number" id="n_userid" class="form-control" placeholder="ID người nhận (Ví dụ: 5)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                        <input type="text" id="n_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea id="n_message" class="form-control" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg" id="btn-submit">
                        <i class="fas fa-paper-plane"></i> Gửi Ngay <i class="fas fa-spinner fa-spin d-none" id="add-spinner"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('notify-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('add-spinner');
    let msgBox = document.getElementById('msg-box');
    
    let uid = document.getElementById('n_userid').value;
    
    if(!uid) {
        if(!confirm("Bạn CHƯA nhập ID User. Hành động này sẽ gửi thông báo cho TẤT CẢ các thành viên trong hệ thống. Tiếp tục?")) return;
    }

    btn.disabled = true; spinner.classList.remove('d-none'); msgBox.style.display = 'none';
    
    let res = await API.post('api/admin.php', {
        action: 'send_notification',
        user_id: uid ? uid : 0,
        title: document.getElementById('n_title').value,
        message: document.getElementById('n_message').value
    });
    
    btn.disabled = false; spinner.classList.add('d-none');
    
    if (res && res.status === 'success') {
        msgBox.innerHTML = res.message;
        msgBox.className = 'alert alert-success';
        msgBox.style.display = 'block';
        document.getElementById('n_title').value = '';
        document.getElementById('n_message').value = '';
        document.getElementById('n_userid').value = '';
    } else {
        msgBox.innerHTML = res ? res.message : 'Lỗi hệ thống';
        msgBox.className = 'alert alert-danger';
        msgBox.style.display = 'block';
    }
});
</script>