<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

$conn = get_db_connection();

$id = intval($_GET['id'] ?? 0);
$post = [
    'id' => 0, 'title' => '', 'excerpt' => '', 'cover_image' => '',
    'content' => '{"blocks":[]}', 'status' => 'draft',
];
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) { $post = $res->fetch_assoc(); }
}

$error = $_SESSION['blog_error'] ?? '';
unset($_SESSION['blog_error']);
$is_edit = $id > 0;
?>

<main class="flex-1 p-8">
    <form action="../api/blog_save.php" method="POST" id="blogForm">
        <input type="hidden" name="id" value="<?php echo (int)$post['id']; ?>">
        <input type="hidden" name="content" id="contentField">
        <input type="hidden" name="status" id="statusField" value="<?php echo htmlspecialchars($post['status']); ?>">

        <header class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                <a href="blogs.php" class="text-gray-400 hover:text-gray-700"><i class="fas fa-arrow-left fa-lg"></i></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo $is_edit ? 'Edit Post' : 'New Post'; ?></h1>
                    <p class="text-gray-500 text-sm">Drag blocks to reorder · press <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">Tab</kbd> to add a block</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="submitPost('draft')" class="px-5 py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition-all">Save Draft</button>
                <button type="button" onclick="submitPost('published')" class="px-5 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold transition-all">
                    <i class="fas fa-paper-plane mr-1"></i> Publish
                </button>
            </div>
        </header>

        <?php if ($error): ?>
        <div class="mb-6 px-5 py-3 bg-red-50 text-red-700 rounded-xl text-sm font-medium"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Editor -->
            <div class="lg:col-span-2 space-y-6">
                <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" placeholder="Post title" required
                    class="w-full text-3xl font-bold text-gray-900 placeholder-gray-300 bg-transparent border-none focus:outline-none focus:ring-0 px-0">
                <div id="editorjs" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 min-h-[500px]"></div>
            </div>

            <!-- Sidebar settings -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Cover Image</h3>
                    <div id="coverPreview" class="mb-3 <?php echo $post['cover_image'] ? '' : 'hidden'; ?>">
                        <img src="<?php echo htmlspecialchars(blog_resolve_url($post['cover_image'])); ?>" id="coverImg" class="w-full rounded-xl object-cover">
                    </div>
                    <input type="hidden" name="cover_image" id="coverField" value="<?php echo htmlspecialchars($post['cover_image']); ?>">
                    <label class="block cursor-pointer text-center px-4 py-3 rounded-xl border-2 border-dashed border-gray-200 text-gray-500 hover:border-orange-300 hover:text-orange-500 transition-all text-sm font-medium">
                        <i class="fas fa-cloud-upload-alt mr-1"></i> <span id="coverLabel"><?php echo $post['cover_image'] ? 'Change cover' : 'Upload cover'; ?></span>
                        <input type="file" accept="image/*" class="hidden" id="coverInput">
                    </label>
                    <p class="text-xs text-gray-400 mt-2">Optional. Falls back to first image in the post.</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3">Excerpt</h3>
                    <textarea name="excerpt" rows="4" placeholder="Short summary for the blog listing (optional)"
                        class="w-full text-sm text-gray-600 border border-gray-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-orange-200"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</main>

<!-- Editor.js + tools -->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.6"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.7"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.10.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.6.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@2.9.3"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.4.2"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@1.4.0"></script>
<script>
    const savedData = <?php echo $post['content'] ?: '{"blocks":[]}'; ?>;

    const editor = new EditorJS({
        holder: 'editorjs',
        placeholder: 'Write your story… press Tab for blocks (image, heading, list, quote)',
        data: savedData,
        tools: {
            header:    { class: Header, inlineToolbar: true, config: { placeholder: 'Heading', levels: [2, 3, 4], defaultLevel: 2 } },
            list:      { class: (window.EditorjsList || window.List), inlineToolbar: true },
            quote:     { class: Quote, inlineToolbar: true },
            marker:    { class: Marker },
            delimiter: { class: Delimiter },
            image: {
                class: ImageTool,
                config: {
                    endpoints: { byFile: '../api/blog_upload.php' },
                    field: 'image',
                    additionalRequestData: {},
                    captionPlaceholder: 'Image caption (optional)'
                }
            }
        }
    });

    async function submitPost(status) {
        try {
            const out = await editor.save();
            document.getElementById('contentField').value = JSON.stringify(out);
            document.getElementById('statusField').value = status;
            if (!document.querySelector('input[name=title]').value.trim()) {
                alert('Please add a title.');
                return;
            }
            document.getElementById('blogForm').submit();
        } catch (e) {
            alert('Could not save: ' + e);
        }
    }

    // Cover image upload (reuses the same endpoint).
    document.getElementById('coverInput').addEventListener('change', async function () {
        if (!this.files.length) return;
        const fd = new FormData();
        fd.append('image', this.files[0]);
        const label = document.getElementById('coverLabel');
        label.textContent = 'Uploading…';
        try {
            const r = await fetch('../api/blog_upload.php', { method: 'POST', body: fd });
            const j = await r.json();
            if (j.success && j.file && j.file.url) {
                document.getElementById('coverField').value = j.file.url;
                document.getElementById('coverImg').src = j.file.url;
                document.getElementById('coverPreview').classList.remove('hidden');
                label.textContent = 'Change cover';
            } else {
                alert(j.message || 'Upload failed');
                label.textContent = 'Upload cover';
            }
        } catch (e) {
            alert('Upload error: ' + e);
            label.textContent = 'Upload cover';
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
