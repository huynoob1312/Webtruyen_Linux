<?php
// File: app/controllers/NovelController.php
require_once 'core/Controller.php';
require_once 'app/models/NovelModel.php';

class NovelController extends Controller {

    public function list() {
        $this->render('novel/list', []);
    }

    public function detail() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Thiếu ID truyện! <a href="index.php">Về trang chủ</a></div>');
        }
        $this->render('novel/detail', []);
    }

    public function read() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Thiếu ID chương! <a href="index.php">Về trang chủ</a></div>');
        }

        $chap_id = intval($_GET['id']);
        $model = new NovelModel();
        
        $chapter = $model->getChapter($chap_id);
        if (!$chapter) {
            die('<div class="container mt-5 alert alert-warning">Chương không tồn tại!</div>');
        }

        $novel_id = $chapter['novel_id'];
        $novel = $model->getDetail($novel_id)['novel'];
        $all_chapters = $model->getChapters($novel_id);

        // Find Prev and Next
        $nav = $model->getPrevNextChapter($novel_id, $chapter['order_index']);
        $prev = $nav['prev'];
        $next = $nav['next'];
        
        $prev_link = $prev ? "index.php?route=novel/read&id=" . $prev['id'] : '#';
        $next_link = $next ? "index.php?route=novel/read&id=" . $next['id'] : '#';
        $disable_prev = $prev ? '' : 'disabled';
        $disable_next = $next ? '' : 'disabled';

        // LƯU LỊCH SỬ
        if (isset($_SESSION['user_id'])) {
            $current_url = $_SERVER['REQUEST_URI'];
            $model->saveHistory($_SESSION['user_id'], $novel_id, $novel['title'], $novel['cover_image'], $chapter['title'], $current_url);
        }

        $this->render('novel/read', [
            'chap_id' => $chap_id,
            'chapter' => $chapter,
            'novel' => $novel,
            'all_chapters' => $all_chapters,
            'novel_id' => $novel_id,
            'prev_link' => $prev_link,
            'next_link' => $next_link,
            'disable_prev' => $disable_prev,
            'disable_next' => $disable_next
        ]);
    }
}
?>
