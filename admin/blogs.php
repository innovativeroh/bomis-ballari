<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

$conn = get_db_connection();

// Ensure table exists (self-healing for first visit).
$conn->query("CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    cover_image VARCHAR(255),
    content LONGTEXT,
    status ENUM('draft','published') DEFAULT 'draft',
    author_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Actions: delete + publish toggle.
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute();
    } elseif ($_GET['action'] === 'toggle') {
        $conn->query("UPDATE blogs SET status = IF(status='published','draft','published') WHERE id = " . $id);
    }
    header("Location: blogs.php");
    exit();
}

$blogs = $conn->query("SELECT id, title, slug, status, cover_image, created_at FROM blogs ORDER BY created_at DESC");

$success = $_SESSION['blog_success'] ?? '';
unset($_SESSION['blog_success']);
?>

<main class="flex-1 p-8">
    <header class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Blogs</h1>
            <p class="text-gray-500">Create and manage articles</p>
        </div>
        <a href="blog_editor.php" class="inline-flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold px-5 py-3 rounded-xl transition-all">
            <i class="fas fa-plus"></i> New Post
        </a>
    </header>

    <?php if ($success): ?>
    <div class="mb-6 px-5 py-3 bg-green-50 text-green-700 rounded-xl text-sm font-medium"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 font-semibold">Post</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold">Date</th>
                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                <?php while($row = $blogs->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <?php if(!empty($row['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars(blog_resolve_url($row['cover_image'])); ?>" class="w-14 h-14 rounded-lg object-cover flex-shrink-0" alt="">
                            <?php else: ?>
                                <div class="w-14 h-14 rounded-lg bg-orange-50 text-orange-400 flex items-center justify-center flex-shrink-0"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($row['title']); ?></p>
                                <p class="text-gray-400 text-xs">/blog/<?php echo htmlspecialchars($row['slug']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if($row['status'] === 'published'): ?>
                            <span class="px-2 py-1 bg-green-50 text-green-600 text-xs font-bold rounded-lg uppercase">Published</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs font-bold rounded-lg uppercase">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-gray-500"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <a href="../blog/<?php echo htmlspecialchars($row['slug']); ?>" target="_blank" class="text-gray-400 hover:text-gray-700 p-2" title="View"><i class="fas fa-eye"></i></a>
                        <a href="?action=toggle&id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 p-2" title="Toggle publish"><i class="fas fa-toggle-on"></i></a>
                        <a href="blog_editor.php?id=<?php echo $row['id']; ?>" class="text-orange-500 hover:text-orange-700 p-2" title="Edit"><i class="fas fa-pen"></i></a>
                        <a href="?action=delete&id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 p-2" title="Delete" onclick="return confirm('Delete this post permanently?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($blogs->num_rows == 0): ?>
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No posts yet. Click "New Post" to write your first article.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
