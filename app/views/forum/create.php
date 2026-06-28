<?php require_once 'app/views/includes/header.php'; ?>

<div class="container py-4" style="min-height: 600px; max-width: 800px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?route=home/index">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=forum/index">Diễn đàn</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo chủ đề mới</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-edit me-2"></i> Tạo Chủ Đề Mới
        </div>
        <div class="card-body p-4">
            <form id="createTopicForm">
                <input type="hidden" name="action" value="create_topic">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Chọn Chuyên Mục <span class="text-danger">*</span></label>
                    <select class="form-select" name="category_id" required>
                        <option value="">-- Chọn chuyên mục --</option>
                        <?php if(!empty($categories)): foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề chủ đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required placeholder="Nhập tiêu đề ngắn gọn, rõ ràng...">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="content" rows="10" required placeholder="Nhập chi tiết nội dung bạn muốn chia sẻ/hỏi đáp..."></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php?route=forum/index" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane"></i> Đăng bài</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createTopicForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

            const formData = new FormData(this);
            try {
                let res = await fetch('api/forum.php', {
                    method: 'POST',
                    body: formData
                });
                let json = await res.json();
                if (json.status === 'success') {
                    // Chuyển hướng đến bài mới tạo
                    window.location.href = 'index.php?route=forum/detail&id=' + json.topic_id;
                } else {
                    alert(json.message || 'Có lỗi xảy ra');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Đăng bài';
                }
            } catch (err) {
                alert('Lỗi kết nối mạng');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Đăng bài';
            }
        });
    }
});
</script>

<?php require_once 'app/views/includes/footer.php'; ?>
