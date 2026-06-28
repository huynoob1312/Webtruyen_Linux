
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/api.js"></script>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <h3><i class="fas fa-book-reader text-primary"></i> Đăng Nhập</h3>
            <p class="text-muted mb-0">Chào mừng bạn quay trở lại</p>
        </div>

        <div class="auth-body pt-0">
            <div id="msg-box" class="alert alert-danger text-center p-2 mb-3 small" style="display:none;"></div>

            <form id="login-form">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" class="form-control" placeholder="Tên đăng nhập" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth mt-2" id="btn-submit">Đăng Nhập <i class="fas fa-spinner fa-spin d-none" id="spinner"></i></button>
            </form>

            <div class="auth-footer">
                Chưa có tài khoản? <a href="index.php?route=auth/register">Đăng ký ngay</a> <br>
                <a href="index.php" class="text-secondary mt-2 d-inline-block small"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
            </div>
        </div>
    </div>

<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    let btn = document.getElementById('btn-submit');
    let spinner = document.getElementById('spinner');
    let msgBox = document.getElementById('msg-box');
    
    btn.disabled = true;
    spinner.classList.remove('d-none');
    msgBox.style.display = 'none';

    let u = document.getElementById('username').value.trim();
    let p = document.getElementById('password').value;

    let res = await API.post('api/auth.php', {
        action: 'login',
        username: u,
        password: p
    });

    btn.disabled = false;
    spinner.classList.add('d-none');

    if (res) {
        if (res.status === 'success') {
            window.location.href = 'index.php';
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