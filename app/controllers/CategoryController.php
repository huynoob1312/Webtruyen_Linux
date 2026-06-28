<?php
// File: app/controllers/CategoryController.php
require_once 'core/Controller.php';
require_once 'app/models/NovelModel.php';

class CategoryController extends Controller {

    public function novel() {
        $slug = $_GET['slug'] ?? '';
        if (empty($slug)) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Cần cung cấp link phân loại chuyên mục chữ! <a href="index.php">Về trang chủ</a></div>');
        }

        $novelModel = new NovelModel();
        // Reuse getByCategory from NovelModel - it handles slug lookup + novel list
        $result = $novelModel->getByCategory($slug, 1, 9999); // get all, no pagination needed on this view

        if (!$result) {
            die('<div class="container mt-5 alert alert-danger">Không tìm thấy thể loại này! <a href="index.php">Về trang chủ</a></div>');
        }

        $this->render('category/novel', [
            'cat_name' => $result['category_name'],
            'novels'   => $result['data'],
        ]);
    }

    public function comic() {
        $slug = $_GET['slug'] ?? '';
        if (empty($slug)) {
            die('<div class="container mt-5 alert alert-danger">Lỗi: Cần cung cấp link thể loại truyện tranh! <a href="index.php">Về trang chủ</a></div>');
        }

        // Gọi API ngoài qua ComicModel
        require_once 'app/models/ComicModel.php';
        $comicModel = new ComicModel();
        $data = $comicModel->getCategoryComics($slug);

        $this->render('category/comic', [
            'cat_name'   => $data['cat_name'],
            'comics'     => $data['comics'],
            'img_domain' => "https://img.otruyenapi.com/uploads/comics/",
        ]);
    }
}
?>
