<?php require_once 'app/views/includes/header.php'; ?>

<div class="container py-4" style="min-height: 600px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?route=home/index">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=forum/index">Diễn đàn</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=forum/index&cat=<?= isset($topic['category_slug']) ? $topic['category_slug'] : '' ?>"><?= htmlspecialchars($topic['category_name'] ?? 'Danh mục') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($topic['title']) ?></li>
        </ol>
    </nav>

    <!-- Original Post -->
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($topic['title']) ?></h5>
            <small><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($topic['created_at'])) ?></small>
        </div>
        <div class="card-body d-flex">
            <!-- Author Info -->
            <div class="text-center pe-4 border-end" style="width: 150px; min-width: 150px;">
                <a href="index.php?route=profile/index&id=<?= $topic['user_id'] ?>">
                    <img src="<?= !empty($topic['avatar']) ? $topic['avatar'] : 'assets/images/default-avatar.png' ?>" class="rounded-circle mb-2 object-fit-cover" width="80" height="80" alt="Avatar" onerror="this.src='https://via.placeholder.com/80'">
                </a>
                <div class="fw-bold">
                    <a href="index.php?route=profile/index&id=<?= $topic['user_id'] ?>" class="text-decoration-none <?= $topic['role'] == 'admin' ? 'text-danger' : ($topic['role'] == 'mod' ? 'text-success' : 'text-primary') ?>">
                        <?= htmlspecialchars($topic['username']) ?>
                    </a>
                </div>
                <small class="text-muted text-uppercase" style="font-size: 11px;"><?= $topic['role'] ?></small>
            </div>
            <!-- Content -->
            <div class="ps-4 flex-grow-1">
                <div class="topic-content" style="white-space: pre-wrap;"><?= htmlspecialchars($topic['content']) ?></div>
            </div>
        </div>
    </div>

    <!-- Replies -->
    <h5 class="mb-3 fw-bold text-secondary"><i class="fas fa-comments"></i> Bình luận (<?= count($posts) ?>)</h5>
    
    <?php if(!empty($posts)): foreach($posts as $idx => $p): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light d-flex justify-content-between py-2">
                <small class="text-muted">#<?= $idx + 1 ?></small>
                <small class="text-muted"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></small>
            </div>
            <div class="card-body d-flex py-3">
                <div class="text-center pe-4 border-end" style="width: 150px; min-width: 150px;">
                    <a href="index.php?route=profile/index&id=<?= $p['user_id'] ?>">
                        <img src="<?= !empty($p['avatar']) ? $p['avatar'] : 'assets/images/default-avatar.png' ?>" class="rounded-circle mb-2 object-fit-cover" width="60" height="60" alt="Avatar" onerror="this.src='https://via.placeholder.com/60'">
                    </a>
                    <div class="fw-bold" style="font-size: 14px;">
                        <a href="index.php?route=profile/index&id=<?= $p['user_id'] ?>" class="text-decoration-none <?= $p['role'] == 'admin' ? 'text-danger' : ($p['role'] == 'mod' ? 'text-success' : 'text-primary') ?>">
                            <?= htmlspecialchars($p['username']) ?>
                        </a>
                    </div>
                </div>
                <div class="ps-4 flex-grow-1">
                    <div class="post-content" id="post-content-<?= $p['id'] ?>" style="white-space: pre-wrap;"><?= htmlspecialchars($p['content']) ?></div>
                    <div id="edit-form-<?= $p['id'] ?>" class="d-none mt-2">
                        <textarea id="edit-input-<?= $p['id'] ?>" class="form-control mb-2" rows="2"></textarea>
                        <div class="text-end">
                            <button class="btn btn-secondary btn-sm me-1" onclick="cancelEdit(<?= $p['id'] ?>)">Hủy</button>
                            <button class="btn btn-success btn-sm" onclick="submitEdit(<?= $p['id'] ?>)">Lưu</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-2 pt-2 border-top small flex-wrap align-items-center">
                <a href="javascript:void(0)" class="text-decoration-none me-3 fw-bold <?= !empty($p['is_liked']) ? 'text-primary' : 'text-secondary' ?>" onclick="likeForumPost(<?= $p['id'] ?>)">
                    👍 <?= isset($p['like_count']) && $p['like_count'] > 0 ? $p['like_count'] : '' ?> <?= !empty($p['is_liked']) ? 'Đã thích' : 'Thích' ?>
                </a>

                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $p['user_id'] || isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'mod'))): ?>
                <div class="ms-auto">
                    <?php if ($_SESSION['user_id'] == $p['user_id']): ?>
                    <a href="javascript:void(0)" class="text-secondary text-decoration-none me-3" onclick="openEditForm(<?= $p['id'] ?>)"><i class="fas fa-edit"></i> Sửa</a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" class="text-danger text-decoration-none" onclick="deletePost(<?= $p['id'] ?>)"><i class="fas fa-trash-alt"></i> Xóa</a>
                </div>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; else: ?>
        <div class="alert alert-light text-center border">Chưa có bình luận nào. Hãy là người đầu tiên tham gia thảo luận!</div>
    <?php endif; ?>

    <!-- Reply Form -->
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white fw-bold"><i class="fas fa-reply text-primary"></i> Viết bình luận</div>
            <div class="card-body">
                <form id="replyForm">
                    <input type="hidden" name="action" value="create_post">
                    <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="4" placeholder="Nhập nội dung bình luận của bạn..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi bình luận</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4 text-center">
            Vui lòng <a href="index.php?route=auth/login" class="fw-bold">Đăng nhập</a> để tham gia bình luận.
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang gửi...';

            const formData = new FormData(this);
            try {
                let res = await fetch('api/forum.php', {
                    method: 'POST',
                    body: formData
                });
                let json = await res.json();
                if (json.status === 'success') {
                    window.location.reload();
                } else {
                    alert(json.message || 'Có lỗi xảy ra');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi bình luận';
                }
            } catch (err) {
                alert('Lỗi kết nối mạng');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi bình luận';
            }
        });
    }
});

function openEditForm(id) {
    let contentDiv = document.getElementById(`post-content-${id}`);
    let currentText = contentDiv.innerText;

    contentDiv.classList.add('d-none');
    
    let editForm = document.getElementById(`edit-form-${id}`);
    let editInput = document.getElementById(`edit-input-${id}`);
    
    editInput.value = currentText;
    editForm.classList.remove('d-none');
}

function cancelEdit(id) {
    document.getElementById(`edit-form-${id}`).classList.add('d-none');
    document.getElementById(`post-content-${id}`).classList.remove('d-none');
}

async function submitEdit(id) {
    let newContent = document.getElementById(`edit-input-${id}`).value;
    if (!newContent.trim()) return alert("Nội dung trống!");

    let fd = new FormData();
    fd.append('action', 'edit_post');
    fd.append('post_id', id);
    fd.append('content', newContent);

    let res = await fetch('api/forum.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.status === 'success') {
        window.location.reload();
    } else {
        alert(json.message || 'Lỗi');
    }
}

async function deletePost(id) {
    if(!confirm('Bạn có chắc muốn xóa bình luận này?')) return;
    let fd = new FormData();
    fd.append('action', 'delete_post');
    fd.append('post_id', id);

    let res = await fetch('api/forum.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.status === 'success') {
        window.location.reload();
    } else {
        alert(json.message || 'Không thể xóa');
    }
}

async function likeForumPost(id) {
    let fd = new FormData();
    fd.append('action', 'like_post');
    fd.append('post_id', id);

    let res = await fetch('api/forum.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.status === 'success') {
        window.location.reload();
    }
}
</script>

<?php require_once 'app/views/includes/footer.php'; ?>
