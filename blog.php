<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/blog_helpers.php';
$conn = get_db_connection();
$posts = $conn ? $conn->query("SELECT title, slug, excerpt, cover_image, created_at FROM blogs WHERE status='published' ORDER BY created_at DESC") : null;
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
  <title>Blog - Birla Open Minds Ballari</title>
  <meta name="description" content="Read our latest insights, tips, and stories about early childhood education and joyful learning.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .scroll-section { opacity: 0; transform: translateY(30px) scale(0.98); transition: opacity 0.8s cubic-bezier(0.22,1,0.36,1), transform 0.8s cubic-bezier(0.22,1,0.36,1); }
    .scroll-section.animate-in { opacity: 1; transform: translateY(0) scale(1); }
    @keyframes float { 0%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-12px) rotate(2deg)} 100%{transform:translateY(0) rotate(0)} }
    .animate-float { animation: float 6s ease-in-out infinite; }
    .blog-card { transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), box-shadow 0.3s cubic-bezier(0.4,0,0.2,1); }
    .blog-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .no-js .scroll-section { opacity: 1 !important; transform: none !important; }
  </style>
  <script>document.documentElement.classList.remove('no-js');</script>
  <link rel="stylesheet" href="responsive.css">
</head>
<body class="bg-[#F7F5F2] min-h-screen flex flex-col">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TNRCGB35" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  <div id="header-placeholder"></div>
  <script src="header.js"></script>

  <main class="flex-grow pt-56 pb-28">
    <div class="max-w-[1200px] mx-auto px-6 text-center pb-20 relative z-10 scroll-section">
      <svg width="64" height="64" viewBox="0 0 64 64" fill="none" class="absolute -left-8 md:-left-16 top-8 md:top-16 w-12 md:w-16 animate-float hidden lg:block">
        <circle cx="18" cy="18" r="3.5" fill="#65B776"/><circle cx="42" cy="14" r="3.5" fill="#65B776"/>
        <path d="M12 36 C 25 52, 45 42, 54 26" stroke="#65B776" stroke-width="4.5" stroke-linecap="round"/>
      </svg>
      <div class="inline-flex items-center gap-2 bg-[#c2410c]/10 px-5 py-2 rounded-full mb-6">
        <div class="w-1.5 h-1.5 rounded-full bg-[#c2410c]"></div>
        <span class="text-[#c2410c] text-[12px] font-bold tracking-widest uppercase mt-0.5">OUR JOURNAL</span>
      </div>
      <h1 class="text-[44px] md:text-[64px] font-bold text-[#111827] leading-[1.1] mb-6 tracking-tight">Latest Insights &amp; Articles</h1>
      <p class="text-gray-500 text-[18px] md:text-[20px] max-w-[650px] mx-auto leading-relaxed">Thoughtful perspectives on parenting, innovative education, and the joyful journey of childhood growth.</p>
    </div>

    <div class="max-w-[1200px] mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php if ($posts && $posts->num_rows): $i = 0; while($row = $posts->fetch_assoc()): ?>
        <article class="blog-card group bg-white rounded-[24px] overflow-hidden flex flex-col shadow-[0_10px_30px_rgba(0,0,0,0.02)] border border-gray-100/50 scroll-section" style="animation-delay: <?php echo ($i++ * 0.1); ?>s;">
          <?php if(!empty($row['cover_image'])): ?>
          <a href="blog/<?php echo htmlspecialchars($row['slug']); ?>" class="block overflow-hidden">
            <img src="<?php echo htmlspecialchars(blog_resolve_url($row['cover_image'])); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy"
                 class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-500">
          </a>
          <?php endif; ?>
          <div class="p-8 flex flex-col flex-grow text-left">
            <span class="text-gray-400 text-[13px] font-medium mb-3 uppercase tracking-wide"><?php echo date('F d, Y', strtotime($row['created_at'])); ?></span>
            <h3 class="text-[22px] md:text-[24px] font-bold text-[#111827] mb-4 leading-tight group-hover:text-[#c2410c] transition-colors duration-300"><?php echo htmlspecialchars($row['title']); ?></h3>
            <p class="text-gray-500 text-[15px] leading-relaxed mb-8 line-clamp-3"><?php echo htmlspecialchars($row['excerpt']); ?></p>
            <a href="blog/<?php echo htmlspecialchars($row['slug']); ?>" aria-label="Read more about <?php echo htmlspecialchars($row['title']); ?>" class="mt-auto inline-flex items-center gap-2 text-[#111827] font-bold text-[15px] group/cta">
              <span class="border-b-2 border-transparent group-hover/cta:border-[#c2410c] transition-all">Read More</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-hover/cta:translate-x-1.5 text-[#c2410c]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
          </div>
        </article>
        <?php endwhile; else: ?>
        <div class="col-span-full text-center text-gray-400 italic py-20">No articles published yet. Check back soon!</div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <div id="footer-placeholder"></div>
  <script src="footer.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('animate-in'); });
      }, { threshold: 0.05 });
      document.querySelectorAll('.scroll-section').forEach(el => observer.observe(el));
    });
  </script>
</body>
</html>
