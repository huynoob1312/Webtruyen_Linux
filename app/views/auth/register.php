
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Thành Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/api.js"></script>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <h3>Đăng Ký</h3>
            <p class="text-muted mb-0">Tạo tài khoản mới</p>
        </div>
        
        <div class="auth-body pt-0">
            <div id="msg-box" class="alert alert-danger text-center p-2 mb-3 small" style="display:none;"></div>
            
            <div id="success-box" class="alert alert-success text-center" style="display:none;">
                <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                Đăng ký thành công! Bạn có thể đăng nhập ngay. <br>
                <a href="index.php?route=auth/login" class="btn btn-success mt-3 w-100">Đăng nhập ngay</a>
            </div>

            <form id="register-form">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Tên đăng nhập</label>
                    <input type="text" id="username" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-secondary">Mật khẩu</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-secondary">Nhập lại MK</label>
                        <input type="password" id="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth mt-2" id="btn-submit">Đăng Ký <i class="fas fa-spinner fa-spin d-none" id="spinner"></i></button>
            </form>
            
            <div class="auth-footer" id="form-footer">
                Đã có tài khoản? <a href="index.php?route=auth/login">Đăng nhập</a> <br>
                <a href="index.php" class="text-secondary mt-2 d-inline-block small"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
            </div>
        </div>
    </div>

<script>
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('spinner');
    let msgBox = document.getElementById('msg-box');
    let successBox = document.getElementById('success-box');
    let form = document.getElementById('register-form');
    let footer = document.getElementById('form-footer');

    btn.disabled = true;
    spinner.classList.remove('d-none');
    msgBox.style.display = 'none';

    let u = document.getElementById('username').value.trim();
    let e_mail = document.getElementById('email').value.trim();
    let p = document.getElementById('password').value;
    let cp = document.getElementById('confirm_password').value;

    if (p !== cp) {
        msgBox.innerHTML = 'Mật khẩu xác nhận không khớp!';
        msgBox.style.display = 'block';
        btn.disabled = false;
        spinner.classList.add('d-none');
        return;
    }

    let res = await API.post('api/auth.php', {
        action: 'register',
        username: u,
        email: e_mail,
        password: p,
        confirm_password: cp
    });

    btn.disabled = false;
    spinner.classList.add('d-none');

    if (res) {
        if (res.status === 'success') {
            form.style.display = 'none';
            footer.style.display = 'none';
            successBox.style.display = 'block';
        } else {
            msgBox.innerHTML = res.message;
            msgBox.style.display = 'block';
        }
    } else {
        msgBox.innerHTML = '❌ Lỗi kết nối API!';
        msgBox.style.display = 'block';
    }
});
</script>
</body>
</html>