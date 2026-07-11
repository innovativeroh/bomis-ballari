<?php
/**
 * Editor.js image upload endpoint (uploadByFile).
 * Returns { success: 1, file: { url } } on success.
 * Admin session required.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => 0, 'message' => 'Unauthorized']);
    exit();
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => 0, 'message' => 'No file uploaded']);
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
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if (!isset($allowed[$mime])) {
    echo json_encode(['success' => 0, 'message' => 'Unsupported file type']);
    exit();
}

$upload_dir = __DIR__ . '/../uploads/blogs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$name = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$dest = $upload_dir . $name;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => 0, 'message' => 'Failed to store file']);
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
