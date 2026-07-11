<?php
/**
 * Editor.js image upload endpoint (uploadByFile).
 * Returns { success: 1, file: { url } } on success.
 * Admin session required.
 */
session_start();
header('Content-Type: application/json');

// Error handler to catch warnings/errors and output JSON
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    http_response_code(500);
    echo json_encode([
        'success' => 0, 
        'message' => "PHP Error: $message in $file on line $line"
    ]);
    exit();
});

// Exception handler to catch uncaught exceptions and output JSON
set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => 0, 
        'message' => "Exception: " . $exception->getMessage()
    ]);
    exit();
});

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => 0, 'message' => 'Unauthorized']);
    exit();
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $err_code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => 0, 'message' => "No file uploaded or upload error code: $err_code"]);
    exit();
}

$file = $_FILES['image'];

// Validate size (max 5MB) and MIME.
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => 0, 'message' => 'File too large (max 5MB)']);
    exit();
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

$mime = null;
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
} elseif (function_exists('mime_content_type')) {
    $mime = mime_content_type($file['tmp_name']);
} elseif (function_exists('getimagesize')) {
    $img_info = getimagesize($file['tmp_name']);
    if ($img_info && isset($img_info['mime'])) {
        $mime = $img_info['mime'];
    }
}

if (!$mime || !isset($allowed[$mime])) {
    echo json_encode(['success' => 0, 'message' => 'Unsupported file type or MIME detection failed. Mime type detected: ' . ($mime ?: 'unknown')]);
    exit();
}

$upload_dir = __DIR__ . '/../uploads/blogs/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

$name = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$dest = $upload_dir . $name;

if (!@move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => 0, 'message' => 'Failed to store file. Please check upload directory permissions.']);
    exit();
}

// Determine the base path of the project relative to DOCUMENT_ROOT.
$doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
$proj_dir = realpath(__DIR__ . '/..');

if ($doc_root && strpos($proj_dir, $doc_root) === 0) {
    $base_path = substr($proj_dir, strlen($doc_root));
} else {
    $base_path = '';
}
$base_path = str_replace('\\', '/', $base_path);
$base_path = rtrim($base_path, '/');

// URL relative to domain root.
echo json_encode([
    'success' => 1,
    'file' => ['url' => $base_path . '/uploads/blogs/' . $name],
]);
