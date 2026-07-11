<?php
/**
 * Create or update a blog post (form POST from admin/blog_editor.php).
 * Redirects back to the admin blog list on success.
 */
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/blogs.php");
    exit();
}

$conn = get_db_connection();

$id      = intval($_POST['id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$cover   = trim($_POST['cover_image'] ?? '');
$content = $_POST['content'] ?? '';
$status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
$author  = intval($_SESSION['admin_id']);

if ($title === '') {
    $_SESSION['blog_error'] = 'Title is required.';
    header("Location: ../admin/blog_editor.php" . ($id ? "?id=$id" : ""));
    exit();
}

// Validate content is JSON; store compact.
$decoded = json_decode($content, true);
if (!is_array($decoded)) {
    $decoded = ['time' => time() * 1000, 'blocks' => [], 'version' => '2.30.0'];
}
$content = json_encode($decoded);

// Auto-derive cover from first image block if none set.
if ($cover === '') {
    foreach (($decoded['blocks'] ?? []) as $b) {
        if (($b['type'] ?? '') === 'image') {
            $cover = $b['data']['file']['url'] ?? ($b['data']['url'] ?? '');
            if ($cover !== '') break;
        }
    }
}

// Auto-excerpt from first paragraph if none set.
if ($excerpt === '') {
    foreach (($decoded['blocks'] ?? []) as $b) {
        if (($b['type'] ?? '') === 'paragraph' && !empty($b['data']['text'])) {
            $excerpt = mb_substr(trim(strip_tags($b['data']['text'])), 0, 180);
            break;
        }
    }
}

if ($id > 0) {
    // Update — keep existing slug unless title changed enough; regenerate uniquely.
    $slug = blog_unique_slug($conn, $title, $id);
    $stmt = $conn->prepare("UPDATE blogs SET title=?, slug=?, excerpt=?, cover_image=?, content=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssi", $title, $slug, $excerpt, $cover, $content, $status, $id);
    $stmt->execute();
    $action = "Updated blog post: $title";
} else {
    $slug = blog_unique_slug($conn, $title);
    $stmt = $conn->prepare("INSERT INTO blogs (title, slug, excerpt, cover_image, content, status, author_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $title, $slug, $excerpt, $cover, $content, $status, $author);
    $stmt->execute();
    $id = $stmt->insert_id;
    $action = "Created blog post: $title";
}

// Activity log (best-effort).
$log = $conn->prepare("INSERT INTO activity_logs (action, admin_id) VALUES (?, ?)");
if ($log) { $log->bind_param("si", $action, $author); $log->execute(); }

$_SESSION['blog_success'] = ($status === 'published' ? 'Post published.' : 'Draft saved.');
header("Location: ../admin/blogs.php");
exit();
