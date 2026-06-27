<h2 class="fw-bold mb-4 text-dark">📂 Quản lý Thể Loại</h2>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white fw-bold">Thêm Thể Loại</div>
            <div class="card-body">
                <form id="add-cat-form">
                    <div class="mb-3">
                        <label>Tên thể loại</label>
                        <input type="text" id="cat-name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100" id="btn-add">
                        Lưu <i class="fas fa-spinner fa-spin d-none" id="add-spinner"></i>
                    </button>
                    <div id="add-msg" class="mt-2 text-center small text-danger" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>Tên</th><th>Slug</th><th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="cat-list">
                        <tr><td colspan="4" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
});

async function loadCategories() {
    let res = await API.get('api/admin.php?action=get_categories');
    if (res && res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="4" class="text-center py-3 text-muted">Chưa có thể loại nào.</td></tr>';
        } else {
            res.data.forEach(c => {
                html += `
                <tr id="cat-row-${c.id}">
                    <td>${c.id}</td>
                    <td><strong>${c.name}</strong></td>
                    <td><code>${c.slug}</code></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(${c.id}, '${c.name}')">Xóa</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('cat-list').innerHTML = html;
    }
}

document.getElementById('add-cat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let nameInput = document.getElementById('cat-name');
    let btnRow = document.getElementById('btn-add');
    let spinner = document.getElementById('add-spinner');
    let msg = document.getElementById('add-msg');
    
    btnRow.disabled = true; spinner.classList.remove('d-none'); msg.style.display = 'none';
    
    let res = await API.post('api/admin.php', { action: 'add_category', name: nameInput.value });
    
    btnRow.disabled = false; spinner.classList.add('d-none');
    
    if (res && res.status === 'success') {
        nameInput.value = '';
        loadCategories(); // reload data
    } else {
        msg.innerText = res ? res.message : "Có lỗi xảy ra!";
        msg.className = "mt-2 text-center small text-danger";
        msg.style.display = 'block';
    }
});

async function deleteCategory(id, name) {
    if (!confirm(`Bạn có chắc muốn xóa thể loại "${name}" không?`)) return;
    
    let res = await API.post('api/admin.php', { action: 'delete_category', id: id });
    if (res && res.status === 'success') {
        let row = document.getElementById('cat-row-'+id);
        if (row) row.remove();
    } else {
        alert(res ? res.message : "Lỗi server");
    }
}
</script>