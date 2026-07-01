<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-gray-800">Quản lý Diễn Đàn</h4>
    </div>

    <!-- Alert Message -->
    <div id="alertMsg"></div>

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Bài đăng (Topics)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th width="50">ID</th>
                            <th>Người đăng</th>
                            <th>Danh mục</th>
                            <th>Tiêu đề</th>
                            <th width="150" class="text-center">Ngày đăng</th>
                            <th width="100" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="topicList">
                        <?php if (isset($topics) && count($topics) > 0): ?>
                            <?php foreach ($topics as $t): ?>
                                <tr>
                                    <td class="text-center"><?= $t['id'] ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($t['username']) ?></span></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($t['category_name']) ?></span></td>
                                    <td>
                                        <a href="index.php?route=forum/detail&id=<?= $t['id'] ?>" target="_blank" class="text-decoration-none fw-bold">
                                            <?= htmlspecialchars($t['title']) ?>
                                        </a>
                                    </td>
                                    <td class="text-center"><small><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></small></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-danger" onclick="deleteTopic(<?= $t['id'] ?>)" title="Xoá chủ đề này">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Chưa có chủ đề nào trên diễn đàn.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
async function deleteTopic(id) {
    if(!confirm('Cảnh báo: Thao tác này sẽ xoá vĩnh viễn chủ đề và toàn bộ bình luận bên trong! Bạn có chắc không?')) return;
    
    try {
        let formData = new FormData();
        formData.append('action', 'delete_topic');
        formData.append('topic_id', id);
        
        let res = await fetch('api/forum.php', {
            method: 'POST',
            body: formData
        });
        let json = await res.json();
        
        if(json.status === 'success') {
            document.getElementById('alertMsg').innerHTML = `<div class="alert alert-success mt-3">Đã xóa thành công!</div>`;
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert(json.message || 'Xóa thất bại');
        }
    } catch(e) {
        alert('Lỗi mạng');
    }
}
</script>
