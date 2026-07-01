<?php
// File: app/models/Model.php
require_once dirname(__DIR__, 2) . '/config/database.php';

class Model {
    protected $conn;

    public function __construct() {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $this->conn = $GLOBALS['conn'];
    }
}
?>
