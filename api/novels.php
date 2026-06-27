<?php
// File: api/novels.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../app/models/NovelModel.php';

header("Content-Type: application/json; charset=UTF-8");

$action = $_GET['action'] ?? '';
$novelModel = new NovelModel();

switch ($action) {
    case 'get_home_novels':
        $data = $novelModel->getHomeNovels();
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'get_list':
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 24;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;

        $res = $novelModel->getList($page, $limit);
        echo json_encode([
            'status' => 'success', 
            'data' => $res['data'], 
            'pagination' => $res['pagination']
        ]);
        break;

    case 'get_by_category':
        $slug = $_GET['slug'] ?? '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 24;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;

        $res = $novelModel->getByCategory($slug, $page, $limit);
        
        if ($res) {
            echo json_encode([
                'status' => 'success', 
                'category_name' => $res['category_name'],
                'data' => $res['data'], 
                'pagination' => $res['pagination']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Category not found']);
        }
        break;

    case 'get_detail':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $res = $novelModel->getDetail($id);
        
        if ($res) {
            echo json_encode([
                'status' => 'success', 
                'data' => $res
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Truyện không tồn tại']);
        }
        break;

    case 'get_chapter':
        $chap_id = isset($_GET['chap_id']) ? intval($_GET['chap_id']) : 0;
        $chapter = $novelModel->getChapter($chap_id);

        if (!$chapter) {
            echo json_encode(['status' => 'error', 'message' => 'Chương không tồn tại']);
            exit;
        }

        $novel_id = $chapter['novel_id'];
        $nav = $novelModel->getPrevNextChapter($novel_id, $chapter['order_index']);
        $prev_id = $nav['prev']['id'] ?? null;
        $next_id = $nav['next']['id'] ?? null;

        // Save History
        if (isset($_SESSION['user_id'])) {
            $u_id = $_SESSION['user_id'];
            $novel_title = $chapter['novel_title'];
            $chap_title = $chapter['title'];
            $chap_url = "index.php?route=novel/read&id=" . $chap_id;
            $img = $chapter['cover_image'] ? $chapter['cover_image'] : 'assets/images/no-image.jpg';
            $novelModel->saveHistory($u_id, $novel_id, $novel_title, $img, $chap_title, $chap_url);
        }

        echo json_encode([
            'status' => 'success', 
            'data' => [
                'chapter' => $chapter,
                'prev_id' => $prev_id,
                'next_id' => $next_id
            ]
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
