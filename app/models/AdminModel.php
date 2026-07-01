<?php
// File: app/models/AdminModel.php
require_once __DIR__ . '/Model.php';

class AdminModel extends Model {

    // --- DASHBOARD ---
    public function getDashboardStats($user_id, $role) {
        $is_admin = ($role === 'admin' || $role === 'mod'); // Note: dashboard_stats in api/admin.php uses $is_admin = ($role==='admin'). Let's pass $is_admin directly.
        $data = [];
        if ($role === 'admin') {
            $data['count_users'] = $this->conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
            $data['count_novels'] = $this->conn->query("SELECT COUNT(*) as total FROM novels")->fetch_assoc()['total'];
            $data['count_comments'] = $this->conn->query("SELECT COUNT(*) as total FROM comments")->fetch_assoc()['total'];
            $view_novel = $this->conn->query("SELECT SUM(views) as total FROM novels")->fetch_assoc()['total'] ?? 0;
            $view_comic = $this->conn->query("SELECT SUM(view_count) as total FROM comic_views")->fetch_assoc()['total'] ?? 0;
            $data['total_views'] = $view_novel + $view_comic;
            
            $sql_reports = "SELECT n.*, u.username, u.avatar FROM notifications n JOIN users u ON n.sender_id = u.id WHERE n.receiver_id = $user_id AND n.type = 'report' AND n.is_read = 0 ORDER BY n.created_at DESC LIMIT 10";
            $reports_res = $this->conn->query($sql_reports);
            $reports = [];
            while ($r = $reports_res->fetch_assoc()) { $reports[] = $r; }
            $data['reports'] = $reports;
        } else {
            $data['count_novels'] = $this->conn->query("SELECT COUNT(*) as total FROM novels")->fetch_assoc()['total'];
            $data['count_cats'] = $this->conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
            $data['count_my_novels'] = $this->conn->query("SELECT COUNT(*) as total FROM novels WHERE posted_by = $user_id")->fetch_assoc()['total'];
        }
        $data['is_admin'] = ($role === 'admin');
        return $data;
    }

    // --- CATEGORIES ---
    public function getCategories() {
        $res = $this->conn->query("SELECT * FROM categories ORDER BY id DESC");
        $cats = [];
        while($r = $res->fetch_assoc()) { $cats[] = $r; }
        return $cats;
    }

    public function addCategory($name, $slug) {
        $check = $this->conn->query("SELECT id FROM categories WHERE slug='$slug'");
        if ($check->num_rows > 0) return ['status' => 'error', 'message' => 'Thể loại hoặc Slug đã tồn tại'];
        
        $stmt = $this->conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        if ($stmt->execute()) return ['status' => 'success', 'message' => 'Đã thêm thành công'];
        return ['status' => 'error', 'message' => 'Lỗi DB'];
    }

    public function deleteCategory($id) {
        return $this->conn->query("DELETE FROM categories WHERE id=$id");
    }

    // --- NOVELS ---
    public function getAdminNovels() {
        $sql = "SELECT n.*, (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM novel_categories nc JOIN categories c ON nc.category_id = c.id WHERE nc.novel_id = n.id) as category_names 
                FROM novels n ORDER BY n.id DESC";
        $res = $this->conn->query($sql);
        $novels = [];
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) { $novels[] = $r; }
        }
        return $novels;
    }

    public function addNovel($title, $slug, $author, $desc, $image, $status, $user_id, $cats_arr) {
        $stmt = $this->conn->prepare("INSERT INTO novels (title, slug, author, description, cover_image, status, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $title, $slug, $author, $desc, $image, $status, $user_id);
        
        if ($stmt->execute()) {
            $novel_id = $this->conn->insert_id;
            if (is_array($cats_arr)) {
                foreach ($cats_arr as $cat_id) {
                    $c_id = intval($cat_id);
                    $this->conn->query("INSERT INTO novel_categories (novel_id, category_id) VALUES ($novel_id, $c_id)");
                }
            }
            return true;
        }
        return false;
    }

    public function getAdminNovel($id) {
        $res = $this->conn->query("SELECT * FROM novels WHERE id=$id");
        if ($res && $res->num_rows > 0) {
            $novel = $res->fetch_assoc();
            $c_res = $this->conn->query("SELECT category_id FROM novel_categories WHERE novel_id=$id");
            $cats = [];
            while($c = $c_res->fetch_assoc()) { $cats[] = $c['category_id']; }
            $novel['categories'] = $cats;
            return $novel;
        }
        return false;
    }

    public function updateNovel($id, $title, $author, $desc, $image, $status, $cats_arr) {
        $stmt = $this->conn->prepare("UPDATE novels SET title=?, author=?, description=?, cover_image=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $author, $desc, $image, $status, $id);
        
        if ($stmt->execute()) {
            $this->conn->query("DELETE FROM novel_categories WHERE novel_id=$id");
            if (is_array($cats_arr)) {
                foreach ($cats_arr as $cat_id) {
                    $c_id = intval($cat_id);
                    $this->conn->query("INSERT INTO novel_categories (novel_id, category_id) VALUES ($id, $c_id)");
                }
            }
            return true;
        }
        return false;
    }

    public function deleteNovel($id) {
        return $this->conn->query("DELETE FROM novels WHERE id=$id");
    }

    // --- CHAPTERS ---
    public function getAdminChapters($novel_id) {
        $res = $this->conn->query("SELECT * FROM novel_chapters WHERE novel_id=$novel_id ORDER BY order_index ASC");
        $chaps = [];
        if ($res && $res->num_rows > 0) {
            while($r = $res->fetch_assoc()) { $chaps[] = $r; }
        }
        return $chaps;
    }

    public function getAdminChapter($id) {
        $res = $this->conn->query("SELECT * FROM novel_chapters WHERE id=$id");
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : false;
    }

    public function deleteChapter($id) {
        return $this->conn->query("DELETE FROM novel_chapters WHERE id=$id");
    }

    public function addChapter($novel_id, $title, $content, $order_index) {
        $stmt = $this->conn->prepare("INSERT INTO novel_chapters (novel_id, title, content, order_index) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $novel_id, $title, $content, $order_index);
        
        if ($stmt->execute()) {
            $this->conn->query("UPDATE novels SET updated_at = NOW() WHERE id = $novel_id");
            
            // Notifications to Followers
            $f_res = $this->conn->query("SELECT user_id FROM novel_favorites WHERE novel_id = $novel_id");
            if ($f_res && $f_res->num_rows > 0) {
                $n_res = $this->conn->query("SELECT title FROM novels WHERE id = $novel_id");
                $novel_title = $n_res->fetch_assoc()['title'] ?? 'Truyện';
                $notif_title = "Chương mới ra lò!";
                $notif_msg = "Truyện '$novel_title' vừa cập nhật $title";
                $nurl = "index.php?route=novel/detail&id=$novel_id";
                
                $notif_stmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, target_url, title, message) VALUES (0, ?, 'system', ?, ?, ?)");
                while ($row = $f_res->fetch_assoc()) {
                    $rid = $row['user_id'];
                    $notif_stmt->bind_param("isss", $rid, $nurl, $notif_title, $notif_msg);
                    $notif_stmt->execute();
                }
            }
            return true;
        }
        return false;
    }

    public function updateChapter($id, $title, $content, $order_index) {
        $stmt = $this->conn->prepare("UPDATE novel_chapters SET title=?, content=?, order_index=? WHERE id=?");
        $stmt->bind_param("ssdi", $title, $content, $order_index, $id);
        return $stmt->execute();
    }

    // --- USERS, COMMENTS, NOTIFS ---
    public function getUsers() {
        $res = $this->conn->query("SELECT id, username, email, avatar, password, role, status, created_at FROM users ORDER BY id DESC");
        $users = [];
        while($r = $res->fetch_assoc()) { 
            if(empty($r['avatar'])) $r['avatar'] = 'https://ui-avatars.com/api/?name='.$r['username'].'&background=random';
            $users[] = $r; 
        }
        return $users;
    }

    public function resetUserPassword($id, $new_pwd) {
        $hashed = password_hash($new_pwd, PASSWORD_DEFAULT);
        return $this->conn->query("UPDATE users SET password = '$hashed' WHERE id = $id");
    }

    public function addUser($username, $email, $role, $pwd) {
        $check = $this->conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ['status' => 'error', 'message' => 'Username hoặc Email đã được sử dụng'];
        }

        $hashed = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $role, $hashed);
        
        if ($stmt->execute()) return ['status' => 'success', 'message' => 'Thêm người dùng mới thành công'];
        return ['status' => 'error', 'message' => 'Lỗi hệ thống khi thêm user'];
    }

    public function editUser($id, $username, $email, $role, $pwd) {
        if (!empty($pwd)) {
            $hashed = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $role, $hashed, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $role, $id);
        }
        return $stmt->execute();
    }

    public function deleteUserAccount($id) {
        return $this->conn->query("DELETE FROM users WHERE id = $id");
    }

    public function updateUserRole($id, $role) {
        return $this->conn->query("UPDATE users SET role = '$role' WHERE id = $id");
    }

    public function toggleUserStatus($id, $status) {
        return $this->conn->query("UPDATE users SET status = '$status' WHERE id = $id AND role != 'admin'");
    }

    public function getGlobalComments() {
        $res = $this->conn->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.id DESC LIMIT 50");
        $cmts = [];
        if ($res && $res->num_rows > 0) {
            while($r = $res->fetch_assoc()) { $cmts[] = $r; }
        }
        return $cmts;
    }

    public function deleteGlobalComment($id) {
        return $this->conn->query("DELETE FROM comments WHERE id = $id");
    }

    public function sendSystemNotification($admin_id, $uid, $title, $msg) {
        if ($uid > 0) {
            $stmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, title, message) VALUES (?, ?, 'system', ?, ?)");
            $stmt->bind_param("iiss", $admin_id, $uid, $title, $msg);
            $stmt->execute();
        } else {
            $users = $this->conn->query("SELECT id FROM users");
            $stmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, title, message) VALUES (?, ?, 'system', ?, ?)");
            while($u = $users->fetch_assoc()) {
                $rid = $u['id'];
                $stmt->bind_param("iiss", $admin_id, $rid, $title, $msg);
                $stmt->execute();
            }
        }
        return true;
    }
}
?>
