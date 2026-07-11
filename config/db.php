<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bomis_ballari_db');

// Dynamically determine project root relative URL path
if (!defined('BASE_PATH')) {
    $doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    $proj_dir = realpath(__DIR__ . '/..');
    if ($doc_root && strpos($proj_dir, $doc_root) === 0) {
        $base_path = substr($proj_dir, strlen($doc_root));
    } else {
        $base_path = '';
    }
    $base_path = str_replace('\\', '/', $base_path);
    $base_path = rtrim($base_path, '/');
    define('BASE_PATH', $base_path);
}

function get_db_connection($include_db = true) {
    if ($include_db) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    }
    
    if ($conn->connect_error) {
        // For debugging, we'll keep the error, but in production we'd log it
        return null;
    }
    return $conn;
}
?>
