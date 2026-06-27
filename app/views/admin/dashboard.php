<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark">Tổng Quan Hệ Thống</h2>
    <span class="badge bg-secondary p-2" id="welcome-badge">Đang tải...</span>
</div>

<div class="row g-4 mb-5" id="stats-container">
    <div class="col-12 text-center py-5"><span class="spinner-border text-primary"></span></div>
</div>

<div class="card shadow-sm border-0 mb-4 d-none" id="reports-section">
    <div class="card-header bg-white py-3 border-bottom"><h5 class="mb-0 fw-bold text-danger">Báo Lỗi Mới</h5></div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="reports-list">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadDashboardStats();
});

async function loadDashboardStats() {
    let res = await API.get('api/admin.php?action=dashboard_stats');
    if (res && res.status === 'success') {
        let d = res.data;
        document.getElementById('welcome-badge').innerText = 'Xin chào: ' + d.username;
        
        if (d.is_admin) {
            document.getElementById('stats-container').innerHTML = `
                <div class="col-md-3"><div class="card card-stat bg-gradient-1"><div class="card-body"><h6 class="text-uppercase mb-2">Thành Viên</h6><h2 class="fw-bold">${Number(d.count_users).toLocaleString()}</h2><i class="fas fa-users stat-icon"></i></div></div></div>
                <div class="col-md-3"><div class="card card-stat bg-gradient-2"><div class="card-body"><h6 class="text-uppercase mb-2">Truyện Chữ</h6><h2 class="fw-bold">${Number(d.count_novels).toLocaleString()}</h2><i class="fas fa-book stat-icon"></i></div></div></div>
                <div class="col-md-3"><div class="card card-stat bg-gradient-3"><div class="card-body"><h6 class="text-uppercase mb-2">Tổng Lượt Xem</h6><h2 class="fw-bold">${Number(d.total_views).toLocaleString()}</h2><i class="fas fa-eye stat-icon"></i></div></div></div>
                <div class="col-md-3"><div class="card card-stat bg-gradient-4"><div class="card-body"><h6 class="text-uppercase mb-2">Bình Luận</h6><h2 class="fw-bold">${Number(d.count_comments).toLocaleString()}</h2><i class="fas fa-comments stat-icon"></i></div></div></div>
            `;
            
            if (d.reports && d.reports.length > 0) {
                document.getElementById('reports-section').classList.remove('d-none');
                let html = '';
                d.reports.forEach(rp => {
                    html += `
                    <div class="list-group-item p-3" id="report-${rp.id}">
                        <div class="d-flex justify-content-between">
                            <strong>${rp.title}</strong>
                            <button class="btn btn-sm btn-success" onclick="markDone(${rp.id})">Đã xử lý</button>
                        </div>
                        <p class="mb-0 text-muted small">${rp.message}</p>
                    </div>`;
                });
                document.getElementById('reports-list').innerHTML = html;
            }
        } else {
            document.getElementById('stats-container').innerHTML = `
                <div class="col-md-4"><div class="card card-stat bg-gradient-1 h-100"><div class="card-body"><h6 class="text-uppercase mb-2">Tổng Số Truyện</h6><h2 class="fw-bold">${Number(d.count_novels).toLocaleString()}</h2><i class="fas fa-book stat-icon"></i></div></div></div>
                <div class="col-md-4"><div class="card card-stat bg-gradient-2 h-100"><div class="card-body"><h6 class="text-uppercase mb-2">Tổng Thể Loại</h6><h2 class="fw-bold">${Number(d.count_cats).toLocaleString()}</h2><i class="fas fa-tags stat-icon"></i></div></div></div>
                <div class="col-md-4"><div class="card card-stat bg-gradient-3 h-100"><div class="card-body"><h6 class="text-uppercase mb-2">Truyện Của Bạn</h6><h2 class="fw-bold">${Number(d.count_my_novels).toLocaleString()}</h2><i class="fas fa-user-edit stat-icon"></i></div></div></div>
            `;
        }
    } else {
        alert("Lỗi tải dữ liệu hoặc bị từ chối!");
        window.location.href = 'index.php';
    }
}

async function markDone(id) {
    if(!confirm('Đã xử lý xong?')) return;
    let res = await API.post('api/admin.php', { action: 'mark_notification_read', id: id });
    if(res && res.status === 'success') {
        document.getElementById('report-'+id).remove();
    }
}
</script>