<?php
require_once __DIR__ . '/Model.php';

class ComicModel extends Model {
    public function getComicDetail($slug) {
        $api_url = "https://otruyenapi.com/v1/api/truyen-tranh/" . $slug;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['data']['item'])) {
                return $data['data']['item'];
            }
        }
        return false;
    }

    public function increaseView($slug, $comicName, $comicThumb) {
        $random_start = rand(1000, 5000);
        $sql_view = "INSERT INTO comic_views (comic_slug, comic_name, comic_thumb, view_count) 
                     VALUES (?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE 
                     view_count = view_count + 1,
                     comic_name = VALUES(comic_name), 
                     comic_thumb = VALUES(comic_thumb)";
        $stmt = $this->conn->prepare($sql_view);
        $stmt->bind_param("sssi", $slug, $comicName, $comicThumb, $random_start);
        $stmt->execute();

        $res = $this->conn->query("SELECT view_count FROM comic_views WHERE comic_slug = '$slug'");
        return $res->fetch_assoc()['view_count'] ?? 0;
    }

    public function getFollowersCount($slug) {
        $res = $this->conn->query("SELECT COUNT(*) as total FROM comic_favorites WHERE comic_slug = '$slug'");
        return $res->fetch_assoc()['total'] ?? 0;
    }

    public function isFavorite($userId, $slug) {
        $check = $this->conn->query("SELECT * FROM comic_favorites WHERE user_id = $userId AND comic_slug = '$slug'");
        return $check->num_rows > 0;
    }

    public function searchComics($keyword, $limit = 3) {
        $api_url = "https://otruyenapi.com/v1/api/tim-kiem?keyword=" . urlencode($keyword);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $comic_list = [];
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['data']['items']) && count($data['data']['items']) > 0) {
                $img_domain = "https://img.otruyenapi.com/uploads/comics/";
                $count = 0;
                foreach ($data['data']['items'] as $comic) {
                    if ($count >= $limit) break;
                    $comic_list[] = [
                        'slug' => $comic['slug'],
                        'name' => $comic['name'],
                        'thumb' => $img_domain . $comic['thumb_url'],
                        'meta' => 'Chapter mới nhất'
                    ];
                    $count++;
                }
            }
        }
        return $comic_list;
    }
    public function getTopViews($limit = 5) {
        $result = $this->conn->query("SELECT comic_slug as slug, comic_name as name, comic_thumb as thumb_url, view_count FROM comic_views ORDER BY view_count DESC LIMIT $limit");
        $data = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Self-healing mechanism if name/thumb is missing
                if (empty($row['name']) || empty($row['thumb_url'])) {
                    $slug_api = $row['slug'];
                    $api_url = "https://otruyenapi.com/v1/api/truyen-tranh/" . $slug_api;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $api_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    if ($response) {
                        $res_data = json_decode($response, true);
                        if (isset($res_data['data']['item'])) {
                            $row['name'] = $res_data['data']['item']['name'];
                            $row['thumb_url'] = "https://img.otruyenapi.com/uploads/comics/" . $res_data['data']['item']['thumb_url'];
                            $stmt_update = $this->conn->prepare("UPDATE comic_views SET comic_name = ?, comic_thumb = ? WHERE comic_slug = ?");
                            $stmt_update->bind_param("sss", $row['name'], $row['thumb_url'], $slug_api);
                            $stmt_update->execute();
                        }
                    }
                }
                $data[] = $row;
            }
        }
        return $data;
    }
    public function getCategoryComics($slug) {
        $api_url = "https://otruyenapi.com/v1/api/the-loai/" . $slug;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        $comics = [];
        $cat_name = $slug;

        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['data']['items'])) {
                $comics = $data['data']['items'];
                $cat_name = $data['data']['titlePage'] ?? $slug;
            }
        }
        return ['comics' => $comics, 'cat_name' => $cat_name];
    }
}
?>
