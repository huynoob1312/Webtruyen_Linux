<?php
require_once __DIR__ . '/Model.php';

class NovelModel extends Model {
    public function getDetail($id) {
        $id = intval($id);
        $stmt = $this->conn->prepare("SELECT * FROM novels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $novel = $stmt->get_result()->fetch_assoc();

        if ($novel) {
            // Increase views
            $this->conn->query("UPDATE novels SET views = views + 1 WHERE id = $id");
            $novel['views']++;
            
            $fav_check = false;
            if (isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                $fav_res = $this->conn->query("SELECT * FROM novel_favorites WHERE user_id = $uid AND novel_id = $id");
                if ($fav_res && $fav_res->num_rows > 0) $fav_check = true;
            }

            $chapters = $this->getChapters($id);
            return [
                'novel' => $novel,
                'chapters' => $chapters,
                'is_favorited' => $fav_check
            ];
        }
        return false;
    }

    public function getChapters($novel_id) {
        $novel_id = intval($novel_id);
        $res = $this->conn->query("SELECT id, title, created_at FROM novel_chapters WHERE novel_id = $novel_id ORDER BY id DESC");
        $chapters = [];
        while($c = $res->fetch_assoc()){
            $chapters[] = $c;
        }
        return $chapters;
    }

    public function getChapter($chapter_id) {
        $chapter_id = intval($chapter_id);
        // Tăng view chương trực tiếp (Không có cột views trong bảng novel_chapters)
        
        $stmt = $this->conn->prepare("SELECT c.*, n.title as novel_title 
                                     FROM novel_chapters c 
                                     JOIN novels n ON c.novel_id = n.id 
                                     WHERE c.id = ?");
        $stmt->bind_param("i", $chapter_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPrevNextChapter($novel_id, $current_idx) {
        $prev = $this->conn->query("SELECT id FROM novel_chapters WHERE novel_id = $novel_id AND order_index < $current_idx ORDER BY order_index DESC LIMIT 1")->fetch_assoc();
        $next = $this->conn->query("SELECT id FROM novel_chapters WHERE novel_id = $novel_id AND order_index > $current_idx ORDER BY order_index ASC LIMIT 1")->fetch_assoc();
        return ['prev' => $prev, 'next' => $next];
    }

    public function saveHistory($uid, $novel_id, $novel_title, $novel_cover, $chap_title, $current_url) {
        $type = 'novel';
        $sql = "INSERT INTO reading_history (user_id, type, item_id, item_name, item_image, chapter_name, chapter_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                chapter_name = VALUES(chapter_name), 
                chapter_url = VALUES(chapter_url), 
                updated_at = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssss", $uid, $type, $novel_id, $novel_title, $novel_cover, $chap_title, $current_url);
        $stmt->execute();
    }
}
?>
