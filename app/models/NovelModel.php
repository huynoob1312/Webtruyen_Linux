<?php
require_once __DIR__ . '/Model.php';

class NovelModel extends Model {
    public function getHomeNovels() {
        $sql = "SELECT n.*, 
                (SELECT title FROM novel_chapters WHERE novel_id = n.id ORDER BY id DESC LIMIT 1) as latest_chap_title,
                (SELECT created_at FROM novel_chapters WHERE novel_id = n.id ORDER BY id DESC LIMIT 1) as latest_chap_date
                FROM novels n 
                ORDER BY n.updated_at DESC LIMIT 12";
        $result = $this->conn->query($sql);
        if (!$result) {
            $sql = "SELECT * FROM novels ORDER BY updated_at DESC LIMIT 12";
            $result = $this->conn->query($sql);
        }
        
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getList($page, $limit) {
        $offset = ($page - 1) * $limit;
        $count_sql = "SELECT COUNT(*) as total FROM novels";
        $total_records = $this->conn->query($count_sql)->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);

        $sql = "SELECT * FROM novels ORDER BY updated_at DESC LIMIT $offset, $limit";
        $result = $this->conn->query($sql);
        
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

    public function getByCategory($slug, $page, $limit) {
        $offset = ($page - 1) * $limit;
        
        $cat_stmt = $this->conn->prepare("SELECT id, name FROM categories WHERE slug = ?");
        $cat_stmt->bind_param("s", $slug);
        $cat_stmt->execute();
        $cat_res = $cat_stmt->get_result();

        if ($cat_res->num_rows == 0) return false;
        
        $cat = $cat_res->fetch_assoc();
        $cat_id = $cat['id'];

        $count_stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM novels n JOIN novel_categories nc ON n.id = nc.novel_id WHERE nc.category_id = ?");
        $count_stmt->bind_param("i", $cat_id);
        $count_stmt->execute();
        $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);

        $stmt = $this->conn->prepare("SELECT n.* FROM novels n JOIN novel_categories nc ON n.id = nc.novel_id WHERE nc.category_id = ? ORDER BY n.updated_at DESC LIMIT ?, ?");
        $stmt->bind_param("iii", $cat_id, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'category_name' => $cat['name'],
            'data'          => $data,
            'pagination'    => [
                'page'         => $page,
                'total_pages'  => $total_pages,
                'total_records'=> $total_records
            ]
        ];
    }

    public function getDetail($id) {
        $id = intval($id);
        $stmt = $this->conn->prepare("SELECT * FROM novels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $novel = $stmt->get_result()->fetch_assoc();

        if ($novel) {
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
    public function searchNovels($keyword, $limit = 5) {
        $sql = "SELECT id, title, cover_image, author FROM novels WHERE title LIKE ? LIMIT ?";
        $likeKey = "%" . $keyword . "%";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $likeKey, $limit);
        $stmt->execute();
        $db_result = $stmt->get_result();

        $novel_list = [];
        if ($db_result->num_rows > 0) {
            while ($row = $db_result->fetch_assoc()) {
                $novel_list[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'cover_image' => $row['cover_image'] ? $row['cover_image'] : 'assets/images/no-image.jpg',
                    'author' => $row['author']
                ];
            }
        }
        return $novel_list;
    }
    public function getCategories() {
        $check_table = $this->conn->query("SHOW TABLES LIKE 'categories'");
        if ($check_table && $check_table->num_rows > 0) {
            $cats = $this->conn->query("SELECT * FROM categories ORDER BY name ASC");
            $data = [];
            if ($cats) {
                while ($row = $cats->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            return $data;
        }
        return [];
    }

    public function getTopViews($limit = 5) {
        $result = $this->conn->query("SELECT * FROM novels ORDER BY views DESC LIMIT $limit");
        $data = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $row['cover_image'] = $row['cover_image'] ? $row['cover_image'] : 'assets/images/no-image.jpg';
                $data[] = $row;
            }
        }
        return $data;
    }
}
?>
