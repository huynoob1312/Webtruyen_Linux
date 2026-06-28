<?php
// File: app/models/UserModel.php
require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    
    // --- AUTHENTICATION ---
    
    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                if ($user['status'] === 'banned') {
                    return ['status' => 'error', 'message' => '🚫 Tài khoản đã bị KHÓA vĩnh viễn!'];
                } else {
                    return ['status' => 'success', 'data' => $user];
                }
            } else {
                return ['status' => 'error', 'message' => '❌ Sai mật khẩu!'];
            }
        }
        return ['status' => 'error', 'message' => '❌ Tài khoản không tồn tại!'];
    }

    public function register($username, $email, $password) {
        $check = $this->conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            return ['status' => 'error', 'message' => 'Tên đăng nhập hoặc Email đã tồn tại!'];
        }
        
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';
        
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_pass, $role);
        
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Đăng ký thành công! Bạn có thể đăng nhập ngay.'];
        } else {
            return ['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $this->conn->error];
        }
    }

    public function checkSessionStatus($user_id) {
        $stmt_status = $this->conn->prepare("SELECT status, role, avatar FROM users WHERE id = ?");
        if ($stmt_status) {
            $stmt_status->bind_param("i", $user_id);
            $stmt_status->execute();
            $res_status = $stmt_status->get_result();

            if ($res_status->num_rows > 0) {
                return $res_status->fetch_assoc();
            }
        }
        return null; // Not found
    }

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

    // --- FAVORITES ---
    
    public function toggleFavoriteNovel($user_id, $novel_id) {
        $check = $this->conn->query("SELECT * FROM novel_favorites WHERE user_id = $user_id AND novel_id = $novel_id");
        if ($check->num_rows > 0) {
            $this->conn->query("DELETE FROM novel_favorites WHERE user_id = $user_id AND novel_id = $novel_id");
            $this->conn->query("UPDATE novels SET favorite_count = favorite_count - 1 WHERE id = $novel_id");
            return 'removed';
        } else {
            $stmt = $this->conn->prepare("INSERT INTO novel_favorites (user_id, novel_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $novel_id);
            $stmt->execute();
            $this->conn->query("UPDATE novels SET favorite_count = favorite_count + 1 WHERE id = $novel_id");
            return 'added';
        }
    }

    public function toggleFavoriteComic($user_id, $slug, $name, $thumb) {
        $check = $this->conn->prepare("SELECT * FROM comic_favorites WHERE user_id = ? AND comic_slug = ?");
        $check->bind_param("is", $user_id, $slug);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $del = $this->conn->prepare("DELETE FROM comic_favorites WHERE user_id = ? AND comic_slug = ?");
            $del->bind_param("is", $user_id, $slug);
            $del->execute();
            return 'removed';
        } else {
            $stmt = $this->conn->prepare("INSERT INTO comic_favorites (user_id, comic_slug, comic_name, comic_thumb) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $slug, $name, $thumb);
            $stmt->execute();
            return 'added';
        }
    }

    // --- NOTIFICATIONS ---
    
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
    // --- PROFILE ---

    public function getProfileById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfileInfo($uid, $username, $email) {
        $check = $this->conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $uid);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ['status' => 'error', 'message' => 'Tên hoặc Email đã tồn tại!'];
        }
        $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $uid);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Cập nhật thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi cập nhật!'];
    }

    public function updateAvatar($uid, $avatarPath) {
        $stmt = $this->conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarPath, $uid);
        return $stmt->execute();
    }

    public function changePassword($uid, $currentPassword, $newPassword, $currentHash) {
        if (!password_verify($currentPassword, $currentHash)) {
            return ['status' => 'error', 'message' => 'Mật khẩu cũ sai!'];
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $uid);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Đổi mật khẩu thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi đổi mật khẩu!'];
    }

    public function getFavoriteNovels($uid) {
        $stmt = $this->conn->prepare("SELECT n.id, n.title, n.cover_image FROM novel_favorites f JOIN novels n ON f.novel_id = n.id WHERE f.user_id = ? ORDER BY f.created_at DESC");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        return $data;
    }

    public function getFavoriteComics($uid) {
        $stmt = $this->conn->prepare("SELECT comic_slug, comic_name, comic_thumb FROM comic_favorites WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        return $data;
    }

    public function getProfileHistory($uid, $limit = 12) {
        // Check table exists first
        $check = $this->conn->query("SHOW TABLES LIKE 'reading_history'");
        if (!$check || $check->num_rows === 0) return [];
        $stmt = $this->conn->prepare("SELECT * FROM reading_history WHERE user_id = ? ORDER BY updated_at DESC LIMIT ?");
        $stmt->bind_param("ii", $uid, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        return $data;
    }

    public function getProfileComments($uid, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT c.*, n.title as novel_title FROM comments c LEFT JOIN novels n ON c.novel_id = n.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $uid, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        return $data;
    }
}
?>
