<?php
// Load environment variables from .env file if it exists
function load_env($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Handle quotes and inline comments
            if (preg_match('/^([\'"])(.*)\1(?:\s*#.*)?$/', $value, $matches)) {
                $value = $matches[2];
            } else {
                if (strpos($value, '#') !== false) {
                    $value = trim(explode('#', $value)[0]);
                }
            }
            
            // Set environment variables
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load from project root
load_env(realpath(__DIR__ . '/../.env'));

// Helper to get environment variables with fallback
function env($key, $default = '') {
    $val = getenv($key);
    if ($val !== false) {
        return $val;
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    return $default;
}

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'bomis_ballari_db'));

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
