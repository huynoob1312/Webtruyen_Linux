<?php
// File: app/controllers/ForumController.php
require_once 'core/Controller.php';
require_once 'app/models/ForumModel.php';

class ForumController extends Controller {
    private $forumModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->forumModel = new ForumModel();
    }

    public function index() {
        $categories = $this->forumModel->getCategories();
        $cat_slug = isset($_GET['cat']) ? $_GET['cat'] : '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        
        $current_cat = null;
        if ($cat_slug) {
            $current_cat = $this->forumModel->getCategoryBySlug($cat_slug);
        }
        
        $topics = [];
        if ($current_cat) {
            $topics = $this->forumModel->getTopics($current_cat['id'], $page, 15);
        } else {
            $topics = $this->forumModel->getTopics(null, $page, 15);
        }

        $this->render('forum/index', [
            'page_title' => 'Diễn Đàn',
            'categories' => $categories,
            'current_cat' => $current_cat,
            'topics' => $topics['data'] ?? [],
            'pagination' => $topics['pagination'] ?? []
        ], true);
    }

    public function detail() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            die("Chủ đề không tồn tại");
        }

        $data = $this->forumModel->getTopic($id);
        if (!$data) {
            die("Chủ đề không tồn tại hoặc đã bị xóa");
        }

        $this->render('forum/detail', [
            'page_title' => $data['topic']['title'],
            'topic' => $data['topic'],
            'posts' => $data['posts']
        ], true);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=home/index");
            exit;
        }

        $categories = $this->forumModel->getCategories();
        
        $this->render('forum/create', [
            'page_title' => 'Tạo Chủ Đề Mới',
            'categories' => $categories
        ], true);
    }
}
?>
