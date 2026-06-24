<?php
// File: core/Controller.php
require_once 'core/View.php';

class Controller {
    /**
     * Render một view cùng với layout
     */
    protected function render($viewPath, $data = [], $layout = 'main') {
        View::render($viewPath, $data, $layout);
    }
    
    /**
     * Redirect sang url khác
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
?>
