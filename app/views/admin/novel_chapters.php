<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">📑 Danh Sách Chương</h2>
    <a href="index.php?route=admin/chapter_add&novel_id=<?= $novel_id ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Chương Mới</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>STT</th><th>ID</th><th>Tiêu đề chương</th><th>Ngày đăng</th><th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody id="chapter-list">
                <tr><td colspan="5" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const novelId = <?= $novel_id ?>;

document.addEventListener('DOMContentLoaded', () => {
    if(novelId === 0) {
        document.getElementById('chapter-list').innerHTML = "<tr><td colspan='5' class='text-center text-danger py-4'>Lỗi: Thiếu Mã Truyện</td></tr>";
        return;
    }
    loadChapters();
});

async function loadChapters() {
    let res = await API.get('api/admin.php?action=get_chapters&novel_id=' + novelId);
    if (res && res.status === 'success') {
        let html = '';
        if(res.data.length === 0) {
            html = "<tr><td colspan='5' class='text-center text-muted py-4'>Chưa có chương nào!</td></tr>";
        } else {
            res.data.forEach((c, index) => {
                html += `
                <tr id="chap-row-${c.id}">
                    <td>${c.order_index}</td>
                    <td>${c.id}</td>
                    <td><strong>${c.title}</strong></td>
                    <td>${c.created_at}</td>
                    <td class="text-center">
                        <a href="index.php?route=admin/chapter_edit&id=${c.id}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-sm btn-danger" onclick="deleteChapter(${c.id}, '${c.title.replace(/'/g, "\\'")}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('chapter-list').innerHTML = html;
    } else {
        document.getElementById('chapter-list').innerHTML = "<tr><td colspan='5' class='text-center text-danger py-4'>Lỗi kết nối API</td></tr>";
    }
}

async function deleteChapter(id, title) {
    if(!confirm(`Xóa chương: ${title}?`)) return;
    let res = await API.post('api/admin.php', { action: 'delete_chapter', id: id });
    if (res && res.status === 'success') {
        document.getElementById('chap-row-'+id).remove();
    } else {
        alert(res ? res.message : "Lỗi server");
    }
}
</script>