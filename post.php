<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/blog_helpers.php';

$conn = get_db_connection();
$slug = $_GET['slug'] ?? '';

$post = null;
if ($conn && $slug !== '') {
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) { $post = $res->fetch_assoc(); }
}

if (!$post) {
    http_response_code(404);
    $title = 'Post Not Found';
} else {
    $title = $post['title'];
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <base href="<?php echo htmlspecialchars(rtrim(BASE_PATH, '/') . '/'); ?>">
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TNRCGB35');</script>
<!-- End Google Tag Manager -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?> - Birla Open Minds Ballari</title>
  <?php if ($post): ?>
  <meta name="description" content="<?php echo htmlspecialchars(mb_substr($post['excerpt'], 0, 160)); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars(mb_substr($post['excerpt'], 0, 160)); ?>">
  <?php if(!empty($post['cover_image'])): ?><meta property="og:image" content="<?php echo htmlspecialchars($post['cover_image']); ?>"><?php endif; ?>
  <?php endif; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .scroll-reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
    .scroll-reveal.animate-in { opacity: 1; transform: none; }
    .no-js .scroll-reveal { opacity: 1 !important; transform: none !important; }
  </style>
  <script>document.documentElement.classList.remove('no-js');</script>
  <link rel="stylesheet" href="responsive.css">
</head>
<body class="bg-[#F7F5F2] min-h-screen flex flex-col">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TNRCGB35" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  <div id="header-placeholder"></div>
  <script src="header.js"></script>

  <main class="flex-grow pt-56 pb-20">
  <?php if (!$post): ?>
    <div class="max-w-[700px] mx-auto px-6 text-center py-20">
      <h1 class="text-[40px] font-bold text-[#111827] mb-4">Post not found</h1>
      <p class="text-gray-500 mb-8">This article may have been moved or unpublished.</p>
      <a href="blog.php" class="inline-flex items-center gap-2 bg-[#c2410c] text-white font-bold px-6 py-3 rounded-full">Back to Blog</a>
    </div>
  <?php else: ?>
    <div class="max-w-[1000px] mx-auto px-6 mb-12 scroll-reveal">
      <a href="blog.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-[#c2410c] text-sm font-semibold mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg> Back to Blog
      </a>
      <span class="text-[#c2410c] text-[13px] font-bold tracking-widest uppercase"><?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
      <h1 class="text-[36px] md:text-[52px] font-bold text-[#111827] leading-[1.1] mt-4 tracking-tight"><?php echo htmlspecialchars($post['title']); ?></h1>
    </div>

    <?php if(!empty($post['cover_image'])): ?>
    <div class="max-w-[1000px] mx-auto px-6 mb-12 scroll-reveal">
      <img src="<?php echo htmlspecialchars(blog_resolve_url($post['cover_image'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full rounded-[24px] shadow-[0_20px_50px_rgba(0,0,0,0.08)] object-cover max-h-[520px]">
    </div>
    <?php endif; ?>

    <article class="max-w-[760px] mx-auto px-6 scroll-reveal">
      <?php echo blog_render_editorjs($post['content']); ?>
    </article>

    <!-- CTA -->
    <div class="max-w-[1000px] mx-auto px-6 mt-20">
      <div class="bg-[#111827] rounded-[28px] px-8 py-14 text-center">
        <h2 class="text-white text-[28px] md:text-[36px] font-bold mb-4">Give your child the best start</h2>
        <p class="text-white/80 text-[17px] mb-8 max-w-[560px] mx-auto">Visit our campus or enquire online. Seats fill quickly — secure your child's future today.</p>
        <a href="enquiry.html" class="inline-flex items-center gap-2 bg-[#c2410c] hover:bg-[#a83409] text-white font-bold px-8 py-4 rounded-full transition-all">Enquire Now</a>
      </div>
    </div>
  <?php endif; ?>
  </main>

  <div id="footer-placeholder"></div>
  <script src="footer.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('animate-in'); });
      }, { threshold: 0.05 });
      document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
    });
  </script>
</body>
</html>
