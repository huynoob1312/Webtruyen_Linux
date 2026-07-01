<?php
require_once __DIR__ . '/Model.php';

class ChatModel extends Model {

    public function getInbox($user_id) {
        // Câu truy vấn này lấy danh sách những người đã từng chat với user_id
        // Sắp xếp theo người có tin nhắn gần nhất và đếm số tin nhắn chưa đọc
        $sql = "SELECT u.id, u.username, u.avatar, 
                    (SELECT message FROM chat_messages c2 
                     WHERE ((c2.sender_id = ? AND c2.receiver_id = u.id) OR (c2.sender_id = u.id AND c2.receiver_id = ?))
                     AND c2.is_deleted = 0
                     ORDER BY c2.id DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM chat_messages c2 
                     WHERE ((c2.sender_id = ? AND c2.receiver_id = u.id) OR (c2.sender_id = u.id AND c2.receiver_id = ?))
                     AND c2.is_deleted = 0
                     ORDER BY c2.id DESC LIMIT 1) as last_time,
                    (SELECT COUNT(*) FROM chat_messages c2 
                     WHERE c2.sender_id = u.id AND c2.receiver_id = ? AND c2.is_read = 0 AND c2.is_deleted = 0) as unread_count
                FROM users u
                WHERE u.id IN (
                    SELECT DISTINCT sender_id FROM chat_messages WHERE receiver_id = ? AND is_deleted = 0
                    UNION
                    SELECT DISTINCT receiver_id FROM chat_messages WHERE sender_id = ? AND is_deleted = 0
                )
                ORDER BY last_time DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getMessages($user1, $user2) {
        $sql = "SELECT * FROM chat_messages 
                WHERE ((sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?))
                   AND is_deleted = 0
                ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function sendMessage($sender_id, $receiver_id, $message) {
        $stmt = $this->conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        return $stmt->execute();
    }

    public function markAsRead($receiver_id, $sender_id) {
        $stmt = $this->conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
        $stmt->bind_param("ii", $receiver_id, $sender_id);
        return $stmt->execute();
    }

    public function checkNewMessages($user_id, $last_id = 0) {
        // Kiểm tra xem có tin nhắn nào mới tới cho user_id (mà id lớn hơn last_id đã tải)
        $sql = "SELECT c.*, u.username, u.avatar 
                FROM chat_messages c
                JOIN users u ON c.sender_id = u.id
                WHERE c.receiver_id = ? AND c.id > ? AND c.is_deleted = 0
                ORDER BY c.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $last_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getTotalUnread($user_id) {
        $sql = "SELECT COUNT(*) as total FROM chat_messages WHERE receiver_id = ? AND is_read = 0 AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function searchUsers($query, $user_id) {
        $sql = "SELECT id, username, avatar FROM users WHERE id = ? OR username LIKE ? LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $like_query = "%$query%";
        $id_query = intval($query);
        $stmt->bind_param("is", $id_query, $like_query);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $users = [];
        while($row = $res->fetch_assoc()) {
            if ($row['id'] != $user_id) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function deleteMessage($message_id, $user_id) {
        // Chỉ người gửi mới được xóa tin nhắn của mình
        $stmt = $this->conn->prepare("UPDATE chat_messages SET is_deleted = 1 WHERE id = ? AND sender_id = ?");
        $stmt->bind_param("ii", $message_id, $user_id);
        return $stmt->execute();
    }
}
?>
