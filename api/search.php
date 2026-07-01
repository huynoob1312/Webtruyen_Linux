<?php
// File: api/search.php
require_once '../app/models/NovelModel.php';
require_once '../app/models/ComicModel.php';

header("Content-Type: application/json; charset=UTF-8");

$keyword = $_POST['keyword'] ?? (isset($_GET['keyword']) ? $_GET['keyword'] : '');
$keyword = trim($keyword);

if (strlen($keyword) < 1) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

$results = [];

// 1. TÌM TRUYỆN CHỮ
$novelModel = new NovelModel();
$novel_list = $novelModel->searchNovels($keyword, 5);

if (!empty($novel_list)) {
    $results[] = [
        'type' => 'novel',
        'title' => '🖋️ Truyện Chữ',
        'items' => $novel_list
    ];
}

// 2. TÌM TRUYỆN TRANH (API)
$comicModel = new ComicModel();
$comic_list = $comicModel->searchComics($keyword, 3);

if (!empty($comic_list)) {
    $results[] = [
        'type' => 'comic',
        'title' => '🖼️ Truyện Tranh',
        'items' => $comic_list
    ];
}

echo json_encode(['status' => 'success', 'data' => $results]);
?>
