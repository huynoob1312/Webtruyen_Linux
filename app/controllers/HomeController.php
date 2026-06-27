<?php
// File: app/controllers/HomeController.php
require_once 'core/Controller.php';

class HomeController extends Controller {
    public function index() {
        $this->render('home/index', []);
    }
}
?>
