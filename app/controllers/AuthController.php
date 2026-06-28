<?php
// File: app/controllers/AuthController.php
require_once 'core/Controller.php';

class AuthController extends Controller {

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }
        $this->render('auth/login', [], false); // Pass false if we don't want to use standard layout (login might have its own HTML shell)
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit;
        }
        $this->render('auth/register', [], false);
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>
