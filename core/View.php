<?php
// File: core/View.php

class View {
    public static function render($viewPath, $data = [], $layout = 'main') {
        // Trích xuất mảng data thành các biến độc lập
        extract($data);
        
        // Render phần nội dung của view trước
        ob_start();
        $viewFile = 'app/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<div class='container mt-5 alert alert-danger'>Lỗi: Không tìm thấy view $viewFile!</div>";
        }
        $content = ob_get_clean(); // Biến $content này sẽ được nhúng vào file layout
        
        // Nạp layout (header + footer), nếu có khai báo false thì không nạp layout
        if ($layout) {
            $layoutFile = 'app/views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
?>
