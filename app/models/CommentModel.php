<?php
// File: app/models/CommentModel.php
require_once __DIR__ . '/Model.php';

class CommentModel extends Model {

    public function addComment($user_id, $type, $obj_id, $content, $parent_id) {
        $novel_id = ($type == 'novel') ? intval($obj_id) : NULL;
        $comic_slug = ($type == 'comic') ? $obj_id : NULL;

        $stmt = $this->conn->prepare("INSERT INTO comments (user_id, novel_id, comic_slug, content, parent_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $user_id, $novel_id, $comic_slug, $content, $parent_id);
        
        if ($stmt->execute()) {
            // Notifications logic
            if ($parent_id > 0) {
                $p_res = $this->conn->query("SELECT user_id FROM comments WHERE id = $parent_id");
                if ($p_res && $p_res->num_rows > 0) {
                    $owner_id = $p_res->fetch_assoc()['user_id'];
                    if ($owner_id != $user_id) {
                        // get username 
                        $u_res = $this->conn->query("SELECT username FROM users WHERE id = $user_id");
                        $replier_name = ($u_res && $u_res->num_rows > 0) ? $u_res->fetch_assoc()['username'] : 'Một người dùng';
                        
                        $ntitle = "Trả lời bình luận mới";
                        $nmsg = "$replier_name vừa trả lời bình luận của bạn.";
                        $nurl = ($type == 'novel') ? "index.php?route=novel/detail&id=$novel_id" : "index.php?route=comic/detail&slug=$comic_slug";
                        
                        $nstmt = $this->conn->prepare("INSERT INTO notifications (sender_id, receiver_id, type, target_url, title, message) VALUES (?, ?, 'reply', ?, ?, ?)");
                        $nstmt->bind_param("iisss", $user_id, $owner_id, $nurl, $ntitle, $nmsg);
                        $nstmt->execute();
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getCommentsList($type, $obj_id, $user_current) {
        $where = ($type == 'novel') ? "c.novel_id = " . intval($obj_id) : "c.comic_slug = '" . $this->conn->real_escape_string($obj_id) . "'";

        $sql = "SELECT c.*, u.username, u.avatar, 
                (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = $user_current) as is_liked
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE $where 
                ORDER BY c.created_at DESC";
                
        $result = $this->conn->query($sql);
        $comments = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $name = $row['username'] ? $row['username'] : 'User';
                $row['avatar'] = $row['avatar'] ? $row['avatar'] : 'https://ui-avatars.com/api/?name='.$name.'&background=random';
                // Note: time_elapsed_string should be kept in frontend or API layer formatting, but we can do it here for now if needed.
                // We'll pass raw created_at back so API can format it.
                $row['is_mine'] = ($row['user_id'] == $user_current) ? true : false;
                $comments[] = $row;
            }
        }
        return $comments;
    }

    public function deleteComment($cmt_id, $user_id, $user_role = 'user') {
        // Find owner
        $check = $this->conn->query("SELECT user_id FROM comments WHERE id = $cmt_id");
        if ($check && $check->num_rows > 0) {
            $owner = $check->fetch_assoc()['user_id'];
            if ($user_role === 'admin' || $user_role === 'mod' || $owner == $user_id) {
                $stmt = $this->conn->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->bind_param("i", $cmt_id);
                return $stmt->execute();
            }
        }
        return false;
    }

    public function editComment($cmt_id, $user_id, $content, $user_role = 'user') {
        $check = $this->conn->query("SELECT user_id FROM comments WHERE id = $cmt_id");
        if ($check && $check->num_rows > 0) {
             $owner = $check->fetch_assoc()['user_id'];
             if ($user_role === 'admin' || $user_role === 'mod' || $owner == $user_id) {
                 $stmt = $this->conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
                 $stmt->bind_param("si", $content, $cmt_id);
                 return $stmt->execute();
             }
        }
        return false;
    }

    public function toggleLike($cmt_id, $user_id) {
        $check = $this->conn->query("SELECT * FROM comment_likes WHERE user_id=$user_id AND comment_id=$cmt_id");
        if ($check && $check->num_rows > 0) {
            $this->conn->query("DELETE FROM comment_likes WHERE user_id=$user_id AND comment_id=$cmt_id");
            $this->conn->query("UPDATE comments SET like_count = like_count - 1 WHERE id=$cmt_id");
        } else {
            $this->conn->query("INSERT INTO comment_likes (user_id, comment_id) VALUES ($user_id, $cmt_id)");
            $this->conn->query("UPDATE comments SET like_count = like_count + 1 WHERE id=$cmt_id");
        }
        return true;
    }
}
?>
