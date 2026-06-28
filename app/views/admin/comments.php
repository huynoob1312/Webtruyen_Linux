<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">💬 Kiểm Duyệt Bình Luận</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>User</th><th>Nội dung</th><th>Target</th><th>Thời gian</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody id="comment-list">
                <tr><td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadComments();
});

async function loadComments() {
    let res = await API.get('api/admin.php?action=get_comments');
    if (res && res.status === 'success') {
        let html = '';
        res.data.forEach(c => {
            let targetType = c.comic_slug ? "Truyện Tranh" : "Truyện Chữ";
            let targetUrl = c.comic_slug ? `index.php?route=comic/detail&slug=${c.comic_slug}#comments` : `index.php?route=novel/detail&id=${c.novel_id}#comments`;
            html += `
            <tr id="cmt-row-${c.id}">
                <td>${c.id}</td>
                <td><strong>${c.username}</strong></td>
                <td><div style="max-height:60px; overflow-y:auto;">${c.content}</div></td>
                <td><span class="badge bg-secondary">${targetType}</span> <a href="${targetUrl}" target="_blank" class="small ms-1">Tới xem</a></td>
                <td>${c.created_at}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteComment(${c.id})"><i class="fas fa-trash"></i> Xóa</button>
                </td>
            </tr>`;
        });
        document.getElementById('comment-list').innerHTML = html || '<tr><td colspan="6" class="text-center">Chưa có bình luận nào</td></tr>';
    } else {
        document.getElementById('comment-list').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi kết nối / Thiếu quyền</td></tr>';
    }
}

async function deleteComment(id) {
    if(!confirm("Xóa vĩnh viễn bình luận này?")) return;
    let res = await API.post('api/admin.php', { action: 'delete_comment', id: id });
    if (res && res.status === 'success') {
        document.getElementById('cmt-row-'+id).remove();
    } else {
        alert(res ? res.message : "Lỗi server");
    }
}
</script>