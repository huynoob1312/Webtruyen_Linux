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

}
?>
