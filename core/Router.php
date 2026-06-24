<?php
// File: core/Router.php

class Router
{
    public function dispatch($route)
    {
        // Mặc định route là home/index nếu trống
        if (empty($route)) {
            $route = 'home/index';
        }

        $parts = explode('/', trim($route, '/'));

        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
        $actionName = !empty($parts[1]) ? str_replace('-', '_', $parts[1]) : 'index';

        $controllerFile = 'app/controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $actionName)) {
                    call_user_func([$controller, $actionName]);
                } else {
                    die("Action '$actionName' không tồn tại trong $controllerName!");
                }
            } else {
                die("Lỗi: Class $controllerName không tồn tại!");
            }
        } else {
            die("Lỗi: Controller $controllerName không tìm thấy tại $controllerFile!");
        }
    }
}
