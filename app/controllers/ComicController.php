<?php
// File: app/controllers/ComicController.php
require_once 'core/Controller.php';
require_once 'app/models/ComicModel.php';

class ComicController extends Controller {

    public function list() {
        $this->render('comic/list', []);
    }

    public function detail() {
        if (!isset($_GET['slug']) || empty($_GET['slug'])) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Thiếu thông tin truyện! <a href="index.php">Về trang chủ</a></div>');
        }
        
        $slug = $_GET['slug'];
        $model = new ComicModel();
        
        $comic = $model->getComicDetail($slug);
        if (!$comic) {
            die('<div class="container mt-5 alert alert-warning">Không tìm thấy truyện này!</div>');
        }
        
        // Cập nhật view
        $img_domain = "https://img.otruyenapi.com/uploads/comics/";
        $comic_thumb_save = $img_domain . $comic['thumb_url'];
        $current_view = $model->increaseView($slug, $comic['name'], $comic_thumb_save);
        
        $total_followers = $model->getFollowersCount($slug);
        
        $is_favorite = false;
        if (isset($_SESSION['user_id'])) {
            $is_favorite = $model->isFavorite($_SESSION['user_id'], $slug);
        }
        
        $chapters = $comic['chapters'][0]['server_data'] ?? [];
        $chap_start = null;
        $chap_latest = null;

        if (!empty($chapters)) {
            $item_head = reset($chapters);
            $item_tail = end($chapters);

            if (floatval($item_head['chapter_name']) < floatval($item_tail['chapter_name'])) {
                $chap_start = $item_head;
                $chap_latest = $item_tail;
            } else {
                $chap_start = $item_tail;
                $chap_latest = $item_head;
            }
        }
        
        $this->render('comic/detail', [
            'slug' => $slug,
            'comic' => $comic,
            'chapters' => $chapters,
            'img_domain' => $img_domain,
            'current_view' => $current_view,
            'total_followers' => $total_followers,
            'is_favorite' => $is_favorite,
            'chap_start' => $chap_start,
            'chap_latest' => $chap_latest
        ]);
    }

    public function read() {
        if (!isset($_GET['api']) || !isset($_GET['slug'])) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Thiếu thông tin chương truyện!</div>');
        }
        $this->render('comic/read', [
            'apiEncoded' => $_GET['api'],
            'comicSlug' => $_GET['slug']
        ]);
    }
}
?>
