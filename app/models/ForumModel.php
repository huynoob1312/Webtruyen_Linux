<?php
require_once __DIR__ . '/Model.php';

class ForumModel extends Model {
    
    public function getCategories() {
        $sql = "SELECT id, name, slug FROM forum_categories ORDER BY id ASC";
        $result = $this->conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getCategoryBySlug($slug) {
        $stmt = $this->conn->prepare("SELECT * FROM forum_categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getTopics($category_id = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Cần đếm tổng số
        if ($category_id) {
            $count_sql = "SELECT COUNT(*) as total FROM forum_topics WHERE category_id = ?";
            $stmtCount = $this->conn->prepare($count_sql);
            $stmtCount->bind_param("i", $category_id);
        } else {
            $count_sql = "SELECT COUNT(*) as total FROM forum_topics";
            $stmtCount = $this->conn->prepare($count_sql);
        }
        $stmtCount->execute();
        $total_records = $stmtCount->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);

        // Lấy danh sách topic cùng thông tin người đăng và số bình luận
        if ($category_id) {
            $sql = "SELECT t.*, u.username, u.avatar, u.role,
                    (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count
                    FROM forum_topics t
                    JOIN users u ON t.user_id = u.id
                    WHERE t.category_id = ?
                    ORDER BY t.created_at DESC
                    LIMIT ?, ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $category_id, $offset, $limit);
        } else {
            $sql = "SELECT t.*, u.username, u.avatar, u.role,
                    (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count
                    FROM forum_topics t
                    JOIN users u ON t.user_id = u.id
                    ORDER BY t.created_at DESC
                    LIMIT ?, ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $offset, $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'total_pages' => $total_pages,
                'total_records' => $total_records
            ]
        ];
    }

    public function getTopic($id) {
        $stmt = $this->conn->prepare("SELECT t.*, u.username, u.avatar, u.role, c.name as category_name, c.slug as category_slug
                                      FROM forum_topics t
                                      JOIN users u ON t.user_id = u.id
                                      JOIN forum_categories c ON t.category_id = c.id
                                      WHERE t.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $topic = $stmt->get_result()->fetch_assoc();

        if ($topic) {
            // Tăng view
            $this->conn->query("UPDATE forum_topics SET views = views + 1 WHERE id = " . intval($id));
            
            // Lấy danh sách comment
            $uid = $_SESSION['user_id'] ?? 0;
            $sqlPosts = "SELECT p.*, u.username, u.avatar, u.role,
                         (SELECT COUNT(*) FROM forum_post_likes fl WHERE fl.post_id = p.id AND fl.user_id = $uid) as is_liked
                         FROM forum_posts p
                         JOIN users u ON p.user_id = u.id
                         WHERE p.topic_id = ?
                         ORDER BY p.created_at ASC";
            $stmtPosts = $this->conn->prepare($sqlPosts);
            $stmtPosts->bind_param("i", $id);
            $stmtPosts->execute();
            $resultPosts = $stmtPosts->get_result();
            $posts = [];
            while ($p = $resultPosts->fetch_assoc()) {
                $posts[] = $p;
            }

            return ['topic' => $topic, 'posts' => $posts];
        }
        return false;
    }

    public function createTopic($category_id, $user_id, $title, $content) {
        $stmt = $this->conn->prepare("INSERT INTO forum_topics (category_id, user_id, title, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $category_id, $user_id, $title, $content);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function createPost($topic_id, $user_id, $content) {
        $stmt = $this->conn->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $topic_id, $user_id, $content);
        return $stmt->execute();
    }

    public function deleteTopic($id) {
        // Có CASCADE bên khoá ngoại nên xoá topic sẽ bay luôn posts
        $stmt = $this->conn->prepare("DELETE FROM forum_topics WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deletePost($id, $user_id, $user_role = 'user') {
        $check = $this->conn->query("SELECT user_id FROM forum_posts WHERE id = $id");
        if ($check && $check->num_rows > 0) {
            $owner = $check->fetch_assoc()['user_id'];
            if ($user_role === 'admin' || $user_role === 'mod' || $user_id == $owner) {
                $stmt = $this->conn->prepare("DELETE FROM forum_posts WHERE id = ?");
                $stmt->bind_param("i", $id);
                return $stmt->execute();
            }
        }
        return false;
    }

    public function editPost($post_id, $content, $user_id, $user_role = 'user') {
        $check = $this->conn->query("SELECT user_id FROM forum_posts WHERE id = $post_id");
        if ($check && $check->num_rows > 0) {
            $owner = $check->fetch_assoc()['user_id'];
            if ($user_role === 'admin' || $user_role === 'mod' || $user_id == $owner) {
                $stmt = $this->conn->prepare("UPDATE forum_posts SET content = ? WHERE id = ?");
                $stmt->bind_param("si", $content, $post_id);
                return $stmt->execute();
            }
        }
        return false;
    }

    public function toggleLikePost($post_id, $user_id) {
        $check = $this->conn->query("SELECT * FROM forum_post_likes WHERE user_id=$user_id AND post_id=$post_id");
        if ($check && $check->num_rows > 0) {
            // Đã like -> Unlike
            $this->conn->query("DELETE FROM forum_post_likes WHERE user_id=$user_id AND post_id=$post_id");
            $this->conn->query("UPDATE forum_posts SET like_count = like_count - 1 WHERE id=$post_id");
        } else {
            // Chưa like -> Like
            $this->conn->query("INSERT INTO forum_post_likes (user_id, post_id) VALUES ($user_id, $post_id)");
            $this->conn->query("UPDATE forum_posts SET like_count = like_count + 1 WHERE id=$post_id");
        }
        return true;
    }

    public function notifyReply($topic_id, $replier_id, $replier_name) {
        $stmt = $this->conn->prepare("SELECT user_id, title FROM forum_topics WHERE id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        $topic = $stmt->get_result()->fetch_assoc();
        if ($topic && $topic['user_id'] != $replier_id) {
            $owner_id = $topic['user_id'];
            $ntitle = "Bình luận mới";
            $nmsg = "$replier_name vừa bình luận trong chủ đề '{$topic['title']}' của bạn.";
            $nurl = "index.php?route=forum/detail&id=$topic_id";
            $nstmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, target_url, title, message) VALUES (?, ?, 'reply', ?, ?, ?)");
            $nstmt->bind_param("iisss", $replier_id, $owner_id, $nurl, $ntitle, $nmsg);
            $nstmt->execute();
        }
    }

    public function getAllTopicsForAdmin() {
        $sql = "SELECT t.*, u.username, c.name as category_name 
                FROM forum_topics t 
                JOIN users u ON t.user_id = u.id 
                JOIN forum_categories c ON t.category_id = c.id 
                ORDER BY t.created_at DESC";
        $result = $this->conn->query($sql);
        $topics = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { $topics[] = $row; }
        }
        return $topics;
    }
}
?>
