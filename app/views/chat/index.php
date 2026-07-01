<?php require_once 'app/views/includes/header.php'; ?>

<style>
.chat-container { height: 600px; display: flex; border: 1px solid #ddd; background: #fff; border-radius: 8px; overflow: hidden; }
.chat-sidebar { width: 300px; border-right: 1px solid #ddd; display: flex; flex-direction: column; }
.chat-main { flex: 1; display: flex; flex-direction: column; background: #f8f9fa; }
.chat-header { padding: 15px; background: #fff; border-bottom: 1px solid #ddd; }
.chat-messages { flex: 1; overflow-y: auto; padding: 20px; }
.chat-input { padding: 15px; background: #fff; border-top: 1px solid #ddd; }
.inbox-list { flex: 1; overflow-y: auto; }
.inbox-item { padding: 15px; border-bottom: 1px solid #f1f1f1; cursor: pointer; display: flex; align-items: center; }
.inbox-item:hover { background: #f8f9fa; }
.inbox-item.active { background: #e9ecef; }
.msg-bubble { max-width: 75%; padding: 10px 15px; border-radius: 15px; margin-bottom: 10px; line-height: 1.4; word-wrap: break-word; }
.msg-out { background: #0084ff; color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
.msg-in { background: #e4e6eb; color: #000; align-self: flex-start; border-bottom-left-radius: 2px; }
.msg-row { display: flex; flex-direction: column; margin-bottom: 10px; }
.btn-delete-msg { color: #ccc; transition: color 0.2s; visibility: hidden; }
.msg-row:hover .btn-delete-msg { visibility: visible; }
.btn-delete-msg:hover { color: #dc3545 !important; }
.unread-badge { display: inline-block; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 11px; margin-left: auto; }
</style>

<div class="container py-4">
    <div class="chat-container shadow-sm">
        
        <!-- Sidebar (Inbox List) -->
        <div class="chat-sidebar">
            <div class="chat-header bg-primary text-white">
                <h5 class="mb-0 fw-bold"><i class="fas fa-inbox me-2"></i> Tin Nhắn</h5>
            </div>
            
            <!-- Ô tìm kiếm để tạo chat mới -->
            <div class="p-2 border-bottom position-relative">
                <input type="text" id="userSearchInput" class="form-control form-control-sm" placeholder="Tìm người dùng..." autocomplete="off">
                <div id="userSearchResults" class="position-absolute w-100 shadow bg-white" style="z-index:100; left:0; top:100%; max-height:200px; overflow-y:auto; display:none;"></div>
            </div>

            <div class="inbox-list" id="inboxList">
                <div class="text-center p-3 text-muted" id="inboxLoading"><span class="spinner-border spinner-border-sm"></span> Đang tải...</div>
                <!-- Render bằng JS -->
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" style="display: none;" id="chatArea">
            <div class="chat-header d-flex align-items-center">
                <a href="#" id="chatAvatarLink">
                    <img src="assets/images/default-avatar.png" id="chatAvatar" class="rounded-circle me-3 object-fit-cover shadow-sm" width="40" height="40" style="transition: transform 0.2s;">
                </a>
                <div>
                    <a href="#" id="chatNameLink" class="text-decoration-none text-dark">
                        <h6 class="mb-0 fw-bold" id="chatName">Tên người dùng</h6>
                    </a>
                </div>
            </div>
            
            <div class="chat-messages d-flex flex-column" id="chatMessages">
                <!-- Data loaded by JS -->
            </div>
            
            <div class="chat-input" id="chatInputArea">
                <form id="sendMessageForm" class="d-flex w-100">
                    <input type="hidden" id="currentReceiverId" name="receiver_id" value="">
                    <input type="hidden" name="action" value="send_message">
                    <input type="text" class="form-control rounded-pill me-2" name="message" id="messageInput" placeholder="Nhập tin nhắn..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary rounded-circle" style="width:40px;height:40px;"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        
        <!-- Placeholder khi chưa chọn chat -->
        <div class="chat-main justify-content-center align-items-center bg-white" id="chatEmpty">
            <div class="text-center text-muted">
                <i class="fas fa-comments fa-4x mb-3 text-light"></i>
                <h5>Chọn một cuộc hội thoại</h5>
            </div>
        </div>

    </div>
</div>

<script>
const myUserId = <?= $_SESSION['user_id'] ?>;
let currentChatUserId = <?= isset($_GET['uid']) ? intval($_GET['uid']) : 0 ?>; // Mở sẵn tin nhắn nếu truyền uid
let pollInterval;

// Load màn hình danh sách inbox
async function loadInbox() {
    try {
        let res = await fetch('api/chat.php?action=get_inbox');
        let json = await res.json();
        let html = '';
        if (json.status === 'success') {
            if(json.data.length > 0) {
                json.data.forEach(item => {
                    let avatar = item.avatar ? item.avatar : 'assets/images/default-avatar.png';
                    let isActive = currentChatUserId == item.id ? 'active' : '';
                    let unreadHtml = item.unread_count > 0 ? `<span class="unread-badge">${item.unread_count}</span>` : '';
                    let summary = item.last_message || '...';
                    if (summary.length > 25) summary = summary.substring(0,25) + '...';

                    html += `
                        <div class="inbox-item ${isActive}" onclick="openChat(${item.id}, '${item.username}', '${avatar}')">
                            <img src="${avatar}" class="rounded-circle me-3 object-fit-cover" width="45" height="45" onerror="this.src='https://via.placeholder.com/45'">
                            <div style="flex:1; min-width:0;">
                                <div class="fw-bold text-truncate">${item.username}</div>
                                <div class="text-muted small text-truncate d-flex align-items-center">
                                    <span>${summary}</span>
                                </div>
                            </div>
                            ${unreadHtml}
                        </div>
                    `;
                    // Nếu url truyền sẵn UID mà tìm thấy trong list, tự động mở
                    if(currentChatUserId == item.id && document.getElementById('chatArea').style.display === 'none') {
                        openChat(item.id, item.username, avatar);
                    }
                });
            } else {
                html = '<div class="p-3 text-center text-muted small">Hộp thư rỗng.</div>';
            }
        }
        document.getElementById('inboxList').innerHTML = html;
        
    } catch(err) {
        console.error(err);
    }
}

// Mở khung chat chi tiết
async function openChat(uid, uname, uavatar) {
    currentChatUserId = uid;
    document.getElementById('currentReceiverId').value = uid;
    document.getElementById('chatName').innerText = uname;
    document.getElementById('chatAvatar').src = uavatar;
    
    // Gán Link Profile
    let profileLink = `index.php?route=profile/index&id=${uid}`;
    document.getElementById('chatAvatarLink').href = profileLink;
    document.getElementById('chatNameLink').href = profileLink;
    
    document.getElementById('chatEmpty').style.display = 'none';
    document.getElementById('chatArea').style.display = 'flex';
    
    // Load class active Inbox
    document.querySelectorAll('.inbox-item').forEach(el => el.classList.remove('active'));
    // (Làm lại list sau cho khớp, tạm thời reload inbox)
    loadInbox();

    await loadMessages(uid);
}

let lastMsgId = 0;

async function loadMessages(uid) {
    document.getElementById('chatMessages').innerHTML = '<div class="text-center p-3"><span class="spinner-border spinner-border-sm"></span></div>';
    try {
        let res = await fetch('api/chat.php?action=get_messages&other_user=' + uid);
        let json = await res.json();
        let html = '';
        if (json.status === 'success' && json.data.length > 0) {
            json.data.forEach(msg => {
                lastMsgId = Math.max(lastMsgId, msg.id);
                if (msg.sender_id == myUserId) {
                    html += `
                        <div class="msg-row" id="msg-${msg.id}">
                            <div class="d-flex align-items-center justify-content-end">
                                <button class="btn btn-link btn-delete-msg p-0 me-2 text-decoration-none" onclick="deleteChatMessage(${msg.id})" title="Xóa tin nhắn">
                                    <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                </button>
                                <div class="msg-bubble msg-out mb-0">${msg.message}</div>
                            </div>
                        </div>`;
                } else {
                    html += `
                        <div class="msg-row">
                            <div class="msg-bubble msg-in" title="${msg.created_at}">${msg.message}</div>
                        </div>`;
                }
            });
        } else {
            html = '<div class="text-center text-muted mt-3">Chưa có tin nhắn nào. Bắt đầu ngay!</div>';
        }
        let box = document.getElementById('chatMessages');
        box.innerHTML = html;
        box.scrollTop = box.scrollHeight;
    } catch(err) {
        console.error(err);
    }
}

// Xử lý gửi tin
document.getElementById('sendMessageForm').addEventListener('submit', async function(e){
    e.preventDefault();
    let msgInput = document.getElementById('messageInput');
    let message = msgInput.value.trim();
    if (!message) return;
    
    // Temp add to UI
    let box = document.getElementById('chatMessages');
    box.innerHTML += `
        <div class="msg-row">
            <div class="msg-bubble msg-out" style="opacity:0.7;">${message}</div>
        </div>`;
    box.scrollTop = box.scrollHeight;
    let formData = new FormData(this);
    msgInput.value = '';

    try {
        let res = await fetch('api/chat.php', { method: 'POST', body: formData });
        let json = await res.json();
        if (json.status === 'success') {
            loadMessages(currentChatUserId);
            loadInbox();
        }
    } catch (err) {
        console.error(err);
    }
});

// AJAX Polling mỗi 3 giây
setInterval(async () => {
    try {
        let res = await fetch('api/chat.php?action=poll&last_id=' + lastMsgId + '&current_chat_with=' + currentChatUserId);
        let json = await res.json();
        let hasNew = false;
        
        if (json.status === 'success') {
            if (json.data && json.data.length > 0) {
                // Nếu tin nhắn mới thuộc về khung chat đang mở => append
                json.data.forEach(msg => {
                    lastMsgId = Math.max(lastMsgId, msg.id);
                    if (currentChatUserId == msg.sender_id) { // Nhận tin người đang mở
                        let box = document.getElementById('chatMessages');
                        // Bỏ cái empty state nếu có
                        if(box.innerHTML.includes('Chưa có tin nhắn nào')) box.innerHTML = '';
                        
                        box.innerHTML += `
                            <div class="msg-row" id="msg-${msg.id}">
                                <div class="msg-bubble msg-in">${msg.message}</div>
                            </div>`;
                        box.scrollTop = box.scrollHeight;
                    }
                });
                hasNew = true;
            }
            if (hasNew || json.unread_total > 0) {
                 loadInbox(); // Tải lại inbox nếu có sự thay đổi
            }
        }
    } catch (err) {
        // im lặng bỏ qua lỗi mạng
    }
}, 3000);

// Logic Tìm kiếm người dùng
let searchTimeout;
document.getElementById('userSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    let q = this.value.trim();
    let resBox = document.getElementById('userSearchResults');
    if (q.length === 0) {
        resBox.style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            let res = await fetch('api/chat.php?action=search_users&q=' + encodeURIComponent(q));
            let json = await res.json();
            if (json.status === 'success' && json.data.length > 0) {
                let html = json.data.map(u => {
                    let avt = u.avatar ? u.avatar : 'assets/images/default-avatar.png';
                    return `
                        <div class="p-2 border-bottom d-flex align-items-center" style="cursor:pointer;" onmouseover="this.style.background='#f1f1f1'" onmouseout="this.style.background=''" onclick="startNewChat(${u.id}, '${u.username}', '${avt}')">
                            <img src="${avt}" class="rounded-circle me-2 object-fit-cover" width="30" height="30" onerror="this.src='https://via.placeholder.com/30'">
                            <span class="small fw-bold">${u.username}</span>
                        </div>
                    `;
                }).join('');
                resBox.innerHTML = html;
                resBox.style.display = 'block';
            } else {
                resBox.innerHTML = '<div class="p-2 text-muted small text-center">Không tìm thấy</div>';
                resBox.style.display = 'block';
            }
        } catch(e) {}
    }, 400);
});

// Click ra ngoài để đóng search
document.addEventListener('click', function(e) {
    if (!document.getElementById('userSearchInput').contains(e.target) && !document.getElementById('userSearchResults').contains(e.target)) {
        document.getElementById('userSearchResults').style.display = 'none';
    }
});

function startNewChat(uid, uname, uavatar) {
    document.getElementById('userSearchInput').value = '';
    document.getElementById('userSearchResults').style.display = 'none';
    currentChatUserId = uid;
    openChat(uid, uname, uavatar);
}

async function deleteChatMessage(id) {
    if (!confirm('Xóa tin nhắn này ở phía bạn?')) return;
    
    let fd = new FormData();
    fd.append('action', 'delete_message');
    fd.append('message_id', id);

    try {
        let res = await fetch('api/chat.php', { method: 'POST', body: fd });
        let json = await res.json();
        if (json.status === 'success') {
            let el = document.getElementById(`msg-${id}`);
            if (el) el.remove();
        } else {
            alert(json.message);
        }
    } catch (e) {}
}

// Chạy lần đầu
document.addEventListener('DOMContentLoaded', () => {
    loadInbox();
});
</script>

<?php require_once 'app/views/includes/footer.php'; ?>
