<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">👥 Quản lý Thành Viên</h2>
    <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Mới</button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 80px;">Avatar</th>
                    <th>Thông tin</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody id="user-list">
                <tr>
                    <td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Sửa/Thêm User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Sửa User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <fieldset id="user-form">
                    <input type="hidden" id="u_id" value="0">

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Username</label>
                        <input type="text" class="form-control" id="u_username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Email</label>
                        <input type="email" class="form-control" id="u_email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1" id="u_password_label">Password Mới (để trống nếu không đổi)</label>
                        <input type="password" class="form-control bg-light" id="u_password" placeholder="********">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Role</label>
                        <select class="form-select" id="u_role">
                            <option value="user">User</option>
                            <option value="mod">Mod</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary px-4" id="btn-save-user" onclick="saveUser()">Cập nhật</button>
            </div>
        </div>
    </div>
</div>

<script>
    let userModal = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
    });

    let allUsers = [];

    async function loadUsers() {
        let res = await API.get('api/admin.php?action=get_users');
        if (res && res.status === 'success') {
            allUsers = res.data;
            let html = '';
            allUsers.forEach(u => {
                let roleBadge = u.role === 'admin' ? '<span class="badge bg-danger text-uppercase">Admin</span>' : (u.role === 'mod' ? '<span class="badge bg-warning text-dark text-uppercase">Mod</span>' : '<span class="badge bg-secondary text-uppercase">User</span>');
                let statusBadge = u.status === 'banned' ? '<span class="badge bg-dark">Banned</span>' : '<span class="badge bg-success">Active</span>';

                // Build absolute URL for avatar if needed
                let avt = u.avatar;

                html += `
            <tr>
                <td class="text-muted">${u.id}</td>
                <td>
                    <img src="${avt}" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
                </td>
                <td>
                    <div class="fw-bold">${u.username}</div>
                    <div class="text-muted small">${u.email}</div>
                </td>
                <td>${roleBadge}</td>
                <td>${statusBadge}</td>
                <td class="text-end">`;

                if (u.role !== 'admin' || u.id == <?php echo $_SESSION['user_id']; ?>) {
                    html += `<button class="btn btn-sm btn-info text-white me-1" onclick="populateEditModal(${u.id})" data-bs-toggle="modal" data-bs-target="#userModal" title="Sửa"><i class="fas fa-edit"></i></button>`;
                } else {
                    html += `<button class="btn btn-sm btn-secondary me-1 disabled" title="Sửa"><i class="fas fa-edit"></i></button>`;
                }

                if (u.role !== 'admin') {
                    if (u.status === 'banned') {
                        html += `<button class="btn btn-sm btn-success me-1" onclick="toggleStatus(${u.id}, 'active')" title="Mở khóa"><i class="fas fa-unlock"></i></button>`;
                    } else {
                        html += `<button class="btn btn-sm btn-dark me-1" onclick="toggleStatus(${u.id}, 'banned')" title="Khóa"><i class="fas fa-ban"></i></button>`;
                    }
                    html += `<button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id}, '${u.username}')" title="Xóa"><i class="fas fa-trash"></i></button>`;
                } else {
                    html += `<button class="btn btn-sm btn-secondary me-1 disabled"><i class="fas fa-ban"></i></button>`;
                    html += `<button class="btn btn-sm btn-secondary disabled"><i class="fas fa-trash"></i></button>`;
                }

                html += `</td></tr>`;
            });
            document.getElementById('user-list').innerHTML = html || '<tr><td colspan="6" class="text-center">Chưa có dữ liệu</td></tr>';
        } else {
            if (res && res.message && (res.message.includes('Quyền') || res.message.includes('Admin'))) {
                alert("Bạn không còn quyền truy cập trang này!");
                window.location.href = 'index.php?route=admin/dashboard';
            } else {
                document.getElementById('user-list').innerHTML = `<br><span class="text-danger">${res ? res.message : 'Thiếu quyền'}</span>`;
            }
        }
    }

    function openAddModal() {
        document.getElementById('u_id').value = '0';
        document.getElementById('u_username').value = '';
        document.getElementById('u_email').value = '';
        document.getElementById('u_password').value = '';
        document.getElementById('u_role').value = 'user';
        document.getElementById('u_role').disabled = false;

        document.getElementById('userModalLabel').innerText = 'Thêm Người Dùng Mới';
        document.getElementById('btn-save-user').innerText = 'Thêm Mới';
        document.getElementById('u_password_label').innerText = 'Password (Bắt buộc)';

        let modalEl = document.getElementById('userModal');
        let modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }

    function populateEditModal(id) {
        let u = allUsers.find(x => x.id == id);
        if (!u) return;

        document.getElementById('u_id').value = u.id;
        document.getElementById('u_username').value = u.username;
        document.getElementById('u_email').value = u.email;
        document.getElementById('u_password').value = '';
        document.getElementById('u_role').value = u.role;

        document.getElementById('userModalLabel').innerText = 'Sửa User';
        document.getElementById('btn-save-user').innerText = 'Cập nhật';
        document.getElementById('u_password_label').innerText = 'Password Mới (để trống nếu không đổi)';

        if (u.role === 'admin' && u.id != <?php echo $_SESSION['user_id']; ?>) {
            document.getElementById('u_role').disabled = true;
        } else {
            document.getElementById('u_role').disabled = false;
        }
    }

    async function saveUser() {
        let btn = document.getElementById('btn-save-user');
        let id = document.getElementById('u_id').value;
        let username = document.getElementById('u_username').value;
        let email = document.getElementById('u_email').value;
        let pwd = document.getElementById('u_password').value;
        let role = document.getElementById('u_role').value;

        if (!username || !email) return alert('Thiếu thông tin');
        if (id == 0 && !pwd) return alert('Vui lòng nhập mật khẩu cho tài khoản mới');

        btn.disabled = true;
        let action = (id == 0) ? 'add_user' : 'edit_user';

        let res = await API.post('api/admin.php', {
            action: action,
            id: id,
            username: username,
            email: email,
            password: pwd,
            role: role
        });
        btn.disabled = false;

        if (res && res.status === 'success') {
            alert(res.message || "Thành công!");
            let modalEl = document.getElementById('userModal');
            let modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            loadUsers();
        } else {
            alert(res ? res.message : "Lỗi server");
        }
    }

    async function toggleStatus(id, newStatus) {
        if (!confirm("Thay đổi trạng thái tài khoản này?")) return;
        let res = await API.post('api/admin.php', {
            action: 'toggle_user_status',
            id: id,
            status: newStatus
        });
        if (res && res.status === 'success') {
            loadUsers();
        } else {
            alert(res ? res.message : "Lỗi server");
        }
    }

    async function deleteUser(id, username) {
        if (!confirm(`BẠN CÓ CHẮC CHẮN MUỐN XÓA TÀI KHOẢN: ${username}?\nHành động này không thể hoàn tác!`)) return;
        let res = await API.post('api/admin.php', {
            action: 'delete_user_account',
            id: id
        });
        if (res && res.status === 'success') {
            loadUsers();
        } else {
            alert(res ? res.message : "Lỗi server");
        }
    }
</script>