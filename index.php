<?php
// File: index.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once 'core/Router.php';

$router = new Router();
$router->dispatch(isset($_GET['route']) ? $_GET['route'] : 'home/index');
?>