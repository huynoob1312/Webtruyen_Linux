<?php
// File: api/sys.php
// Quản lý các logic hệ thống chung như danh mục, cài đặt...

require_once '../app/models/NovelModel.php';
require_once '../app/models/ComicModel.php';

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing action']);
    exit;
}

$action = $_GET['action'];

switch ($action) {
    case 'get_top_views':
        $type = isset($_GET['type']) ? $_GET['type'] : 'novel';
        if ($type === 'novel') {
            $novelModel = new NovelModel();
            $data = $novelModel->getTopViews(5);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else if ($type === 'comic') {
            $comicModel = new ComicModel();
            $data = $comicModel->getTopViews(5);
            echo json_encode(['status' => 'success', 'data' => $data]);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
