<div class="card mt-5 shadow-sm">
    <div class="card-header bg-white fw-bold py-3 border-bottom">
        💬 Bình luận (<span id="total-cmt">0</span>)
    </div>
    <div class="card-body">
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="d-flex mb-4">
                <?php 
                $my_avt = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username'].'&background=random'; 
                ?>
                <a href="index.php?route=profile/index&id=<?= $_SESSION['user_id'] ?>">
                    <img src="<?= $my_avt ?>" class="rounded-circle me-3 border" width="40" height="40" style="object-fit:cover;">
                </a>
                
                <div class="flex-grow-1">
                    <textarea id="main-cmt-input" class="form-control mb-2" rows="2" placeholder="Bình luận gì đó về truyện này..."></textarea>
                    <button class="btn btn-primary btn-sm float-end px-4" onclick="postComment(0)">Gửi</button>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary text-center">
                Vui lòng <a href="index.php?route=auth/login" class="fw-bold">Đăng nhập</a> để bình luận.
            </div>
        <?php endif; ?>

        <hr>

        <div id="comment-list">
            <p class="text-center text-muted py-3">Đang tải bình luận...</p>
        </div>
    </div>
</div>

<script>
// Nhận biến từ PHP truyền vào
const CMT_TYPE = '<?= $cmt_type ?>';
const CMT_OBJ_ID = '<?= $cmt_obj_id ?>';

// 1. Tải danh sách
function loadComments() {
    let fd = new FormData();
    fd.append('action', 'list');
    fd.append('type', CMT_TYPE);
    fd.append('obj_id', CMT_OBJ_ID);

    fetch('api/comments.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.status !== 'success') return;
        let data = res.data;
        let html = '';
        let count = 0;
        
        if (data.length === 0) {
            html = '<p class="text-center text-muted py-3">Chưa có bình luận nào. Hãy là người đầu tiên!</p>';
        } else {
            function renderTree(cmt, level) {
                let out = renderComment(cmt);
                if(cmt.replies && cmt.replies.length > 0) {
                    let marginLeft = level === 0 ? '55px' : '20px';
                    out += `<div style="margin-left: ${marginLeft}; border-left: 3px solid #f0f2f5; padding-left: 15px;">`;
                    cmt.replies.forEach(r => {
                        count++;
                        out += renderTree(r, level + 1);
                    });
                    out += `</div>`;
                }
                return out;
            }

            data.forEach(cmt => {
                count++;
                html += renderTree(cmt, 0);
            });
        }
        document.getElementById('comment-list').innerHTML = html;
        document.getElementById('total-cmt').innerText = count;
    });
}

// 2. Render HTML (Có link profile + Nút Sửa/Xóa)
function renderComment(cmt) {
    let likeColor = cmt.is_liked > 0 ? 'text-primary' : 'text-secondary';
    let likeText = cmt.is_liked > 0 ? 'Đã thích' : 'Thích';
    
    // Nút Sửa/Xóa (Chỉ hiện nếu là comment của mình)
    let actionButtons = '';
    if (cmt.is_mine) {
        actionButtons = `
            <span class="mx-2 text-muted">•</span>
            <a href="javascript:void(0)" class="text-muted text-decoration-none small" onclick="openEditForm(${cmt.id})">Sửa</a>
            <span class="mx-1 text-muted">•</span>
            <a href="javascript:void(0)" class="text-danger text-decoration-none small" onclick="deleteComment(${cmt.id})">Xóa</a>
        `;
    }

    // Ô trả lời (Ẩn mặc định)
    let replyBox = '';
    <?php if(isset($_SESSION['user_id'])): ?>
    replyBox = `
        <div id="reply-box-${cmt.id}" class="d-none mt-2">
            <textarea id="reply-input-${cmt.id}" class="form-control mb-2" rows="1" placeholder="Trả lời ${cmt.username}..."></textarea>
            <button class="btn btn-primary btn-sm" onclick="postComment(${cmt.id})">Gửi trả lời</button>
        </div>
    `;
    <?php endif; ?>

    return `
        <div class="d-flex mb-3 fade-in" id="comment-row-${cmt.id}">
            <a href="index.php?route=profile/index&id=${cmt.user_id}">
                <img src="${cmt.avatar}" class="rounded-circle me-3 border" width="40" height="40" style="object-fit:cover;">
            </a>
            
            <div class="flex-grow-1">
                <div class="bg-light p-3 rounded-3" style="background-color: #f0f2f5 !important;">
                    
                    <a href="index.php?route=profile/index&id=${cmt.user_id}" class="text-decoration-none text-dark fw-bold d-block mb-1">
                        ${cmt.username}
                    </a>
                    
                    <span class="text-break" id="content-${cmt.id}">${cmt.content}</span>
                    
                    <div id="edit-form-${cmt.id}" class="d-none mt-2">
                        <textarea id="edit-input-${cmt.id}" class="form-control mb-2" rows="2"></textarea>
                        <div class="text-end">
                            <button class="btn btn-secondary btn-sm me-1" onclick="cancelEdit(${cmt.id})">Hủy</button>
                            <button class="btn btn-success btn-sm" onclick="submitEdit(${cmt.id})">Lưu</button>
                        </div>
                    </div>

                </div>
                
                <div class="small mt-1 ms-2">
                    <span class="text-muted me-3">${cmt.time_ago}</span>
                    
                    <a href="javascript:void(0)" class="fw-bold text-decoration-none me-3 ${likeColor}" onclick="likeComment(${cmt.id})">
                        👍 ${cmt.like_count > 0 ? cmt.like_count : ''} ${likeText}
                    </a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="javascript:void(0)" class="fw-bold text-decoration-none text-secondary" onclick="toggleReply(${cmt.id})">
                        💬 Trả lời
                    </a>
                    <?php endif; ?>
                    
                    ${actionButtons}
                </div>
                ${replyBox}
            </div>
        </div>
    `;
}

// 3. Gửi Comment
function postComment(parentId) {
    let inputId = parentId === 0 ? 'main-cmt-input' : `reply-input-${parentId}`;
    let content = document.getElementById(inputId).value;

    if (!content.trim()) return alert('Vui lòng nhập nội dung!');

    let fd = new FormData();
    fd.append('action', 'add');
    fd.append('type', CMT_TYPE);
    fd.append('obj_id', CMT_OBJ_ID);
    fd.append('content', content);
    fd.append('parent_id', parentId);

    fetch('api/comments.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById(inputId).value = ''; 
            // Nếu là reply thì ẩn form đi sau khi gửi
            if(parentId !== 0) toggleReply(parentId);
            loadComments(); 
        } else {
            alert(data.message);
        }
    });
}

// 4. Like Comment
function likeComment(cmtId) {
    let fd = new FormData();
    fd.append('action', 'like');
    fd.append('cmt_id', cmtId);
    fetch('api/comments.php', { method: 'POST', body: fd }).then(() => loadComments());
}

// 5. Ẩn/Hiện form trả lời
function toggleReply(id) {
    document.getElementById(`reply-box-${id}`).classList.toggle('d-none');
}

// 6. Xóa Comment
function deleteComment(id) {
    if(!confirm('Xóa bình luận này?')) return;

    let fd = new FormData();
    fd.append('action', 'delete');
    fd.append('cmt_id', id);

    fetch('api/comments.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') loadComments();
        else alert(data.message);
    });
}

// ===========================================
// 7. CÁC HÀM SỬA BÌNH LUẬN (INLINE)
// ===========================================

function openEditForm(id) {
    let contentSpan = document.getElementById(`content-${id}`);
    let currentText = contentSpan.innerText;

    contentSpan.classList.add('d-none'); // Ẩn text cũ

    let editForm = document.getElementById(`edit-form-${id}`);
    let editInput = document.getElementById(`edit-input-${id}`);
    
    editInput.value = currentText;
    editForm.classList.remove('d-none'); // Hiện form
}

function cancelEdit(id) {
    document.getElementById(`edit-form-${id}`).classList.add('d-none'); // Ẩn form
    document.getElementById(`content-${id}`).classList.remove('d-none'); // Hiện lại text cũ
}

function submitEdit(id) {
    let input = document.getElementById(`edit-input-${id}`);
    let newContent = input.value;

    if (!newContent.trim()) return alert("Nội dung không được để trống!");

    let fd = new FormData();
    fd.append('action', 'edit');
    fd.append('cmt_id', id);
    fd.append('content', newContent);

    fetch('api/comments.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            loadComments(); 
        } else {
            alert(data.message);
        }
    });
}

// Chạy khi load trang
loadComments();
</script>

<style>
    .fade-in { animation: fadeIn 0.5s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .border-bottom { border-bottom: 1px solid #eee !important; }
</style>