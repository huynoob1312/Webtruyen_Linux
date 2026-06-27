<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">📚 Danh sách Truyện</h2>
    <a href="index.php?route=admin/novel_add" class="btn btn-success"><i class="fas fa-plus-circle"></i> Thêm Mới</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Ảnh</th><th>Tên Truyện</th><th>Thể loại</th><th>Trạng thái</th><th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody id="novels-list">
                <tr><td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadNovels();
});

async function loadNovels() {
    let res = await API.get('api/admin.php?action=get_novels');
    if (res && res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="text-center py-3 text-muted">Chưa có truyện nào.</td></tr>';
        } else {
            res.data.forEach(n => {
                let statusBadge = n.status === 'completed' ? '<span class="badge bg-success">Hoàn thành</span>' : '<span class="badge bg-warning text-dark">Đang tiến hành</span>';
                html += `
                <tr id="novel-row-${n.id}">
                    <td>${n.id}</td>
                    <td><img src="${n.cover_image || 'assets/images/no-image.jpg'}" style="width: 40px; height: 60px; object-fit: cover;" class="rounded border shadow-sm"></td>
                    <td><strong>${n.title}</strong><br><small class="text-muted"><i class="fas fa-pen-nib"></i> ${n.author}</small></td>
                    <td><small>${n.category_names || 'Chưa phân loại'}</small></td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <a href="index.php?route=admin/novel_chapters&novel_id=${n.id}" class="btn btn-sm btn-info text-white" title="Quản lý chương"><i class="fas fa-list"></i></a>
                        <a href="index.php?route=admin/novel_edit&id=${n.id}" class="btn btn-sm btn-warning" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-danger" onclick="deleteNovel(${n.id}, '${n.title.replace(/'/g, "\\'")}')" title="Xóa"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('novels-list').innerHTML = html;
    } else {
        document.getElementById('novels-list').innerHTML = '<tr><td colspan="6" class="text-center py-3 text-danger">Lỗi tải dữ liệu.</td></tr>';
    }
}

async function deleteNovel(id, title) {
    if (!confirm(`Bạn có cực kỳ chắc chắn muốn xóa truyện "${title}" và tất cả các chương của nó không?`)) return;
    
    let res = await API.post('api/admin.php', { action: 'delete_novel', id: id });
    if (res && res.status === 'success') {
        let row = document.getElementById('novel-row-'+id);
        if (row) row.remove();
    } else {
        alert(res ? res.message : "Lỗi server");
    }
}
</script>