<?php
// File: app/controllers/SearchController.php
require_once 'core/Controller.php';
require_once 'app/models/NovelModel.php';
require_once 'app/models/ComicModel.php';

class SearchController extends Controller {

    public function index() {
        $keyword = $_GET['q'] ?? '';
        $keyword = trim($keyword);

        if ($keyword == '') {
            header("Location: index.php");
            exit;
        }

        $novelModel = new NovelModel();
        $db_results = $novelModel->searchNovels($keyword, 50); // trả array, không phải mysqli_result

        $comicModel = new ComicModel();
        $img_domain = "https://img.otruyenapi.com/uploads/comics/";
        $api_results = $comicModel->searchComics($keyword, 20);

        $this->render('search/index', [
            'keyword'     => $keyword,
            'db_results'  => $db_results,
            'api_results' => $api_results,
            'img_domain'  => $img_domain
        ]);
    }
}
?>
