<?php
// File: app/models/UserModel.php
require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    // --- HISTORY --- 
    public function addHistory($user_id, $type, $item_id, $item_name, $item_image, $chapter_name, $chapter_url) {
        $sql = "INSERT INTO reading_history (user_id, type, item_id, item_name, item_image, chapter_name, chapter_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                chapter_name = VALUES(chapter_name), 
                chapter_url = VALUES(chapter_url), 
                updated_at = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $type, $item_id, $item_name, $item_image, $chapter_name, $chapter_url);
        return $stmt->execute();
    }

    public function getHistory($user_id, $limit = 5) {
        $stmt = $this->conn->prepare("SELECT * FROM reading_history WHERE user_id = ? ORDER BY updated_at DESC LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function deleteHistoryOne($user_id, $id) {
        $stmt = $this->conn->prepare("DELETE FROM reading_history WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }

    public function deleteHistoryAll($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM reading_history WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
     public function notifyAdmins($sender_id, $title, $msg) {
        $admins = $this->conn->query("SELECT id FROM users WHERE role = 'admin'");
        if ($admins->num_rows > 0) {
            $stmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, title, message) VALUES (?, ?, 'report', ?, ?)");
            while ($row = $admins->fetch_assoc()) {
                $admin_id = $row['id'];
                $stmt->bind_param("iiss", $sender_id, $admin_id, $title, $msg);
                $stmt->execute();
            }
            return true;
        }
        return false;
    }

    public function getNotifications($user_id) {
        $sql = "SELECT n.*, u.username, u.avatar, u.role FROM notifications n LEFT JOIN users u ON n.sender_id = u.id WHERE n.receiver_id = ? ORDER BY n.created_at DESC LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifs = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] == 'system') {
                $row['sender_name'] = 'Hệ Thống';
                $row['sender_avatar'] = 'assets/system_logo.jpg';
            } else {
                $row['sender_name'] = $row['username'] ?? 'Người dùng ẩn';
                $row['sender_avatar'] = $row['avatar'] ?? 'https://ui-avatars.com/api/?name='.$row['sender_name'];
            }
            $row['time_ago'] = date('H:i d/m', strtotime($row['created_at'])); // Basic format, could use time_elapsed_string frontend or here
            $notifs[] = $row;
        }

        $count_sql = $this->conn->query("SELECT COUNT(*) as total FROM notifications WHERE receiver_id = $user_id AND is_read = 0");
        $unread_count = $count_sql->fetch_assoc()['total'];

        return ['notifications' => $notifs, 'unread' => $unread_count];
    }

    public function markNotifRead($user_id, $notif_id) {
        return $this->conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND receiver_id = $user_id");
    }

    public function deleteNotif($user_id, $notif_id) {
        return $this->conn->query("DELETE FROM notifications WHERE id = $notif_id AND receiver_id = $user_id");
    }

    public function deleteAllNotifs($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE receiver_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
}
?>
