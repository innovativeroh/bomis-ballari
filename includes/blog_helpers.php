<?php
/**
 * Shared blog helpers: slug generation + Editor.js JSON -> HTML renderer.
 * Used by frontend (post.php) and admin (blogs.php).
 */

if (!function_exists('blog_resolve_url')) {
    function blog_resolve_url($url) {
        if (empty($url) || strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }
        $path = ltrim($url, '/');
        if (defined('BASE_PATH') && BASE_PATH !== '') {
            $sub_dir = ltrim(BASE_PATH, '/');
            if (strpos($path, $sub_dir) === 0) {
                return '/' . $path;
            }
            return BASE_PATH . '/' . $path;
        }
        return '/' . $path;
    }
}

if (!function_exists('blog_slugify')) {
    function blog_slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text ?: 'post';
    }
}

if (!function_exists('blog_unique_slug')) {
    function blog_unique_slug($conn, $base, $ignore_id = 0) {
        $slug = blog_slugify($base);
        $candidate = $slug;
        $i = 2;
        while (true) {
            $stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ? LIMIT 1");
            $stmt->bind_param("si", $candidate, $ignore_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                return $candidate;
            }
            $candidate = $slug . '-' . $i;
            $i++;
        }
    }
}

if (!function_exists('blog_render_editorjs')) {
    /**
     * Render Editor.js saved data (JSON string or decoded array) to safe HTML.
     */
    function blog_render_editorjs($data) {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (!is_array($data) || empty($data['blocks'])) {
            return '';
        }
        $html = '';
        foreach ($data['blocks'] as $block) {
            $type = $block['type'] ?? '';
            $d = $block['data'] ?? [];
            switch ($type) {
                case 'header':
                    $level = min(max((int)($d['level'] ?? 2), 1), 6);
                    $sizes = [1 => 'text-[40px]', 2 => 'text-[30px]', 3 => 'text-[24px]', 4 => 'text-[20px]', 5 => 'text-[18px]', 6 => 'text-[16px]'];
                    $cls = ($sizes[$level] ?? 'text-[24px]') . ' font-bold text-[#111827] mt-10 mb-4 leading-tight';
                    $html .= "<h{$level} class=\"{$cls}\">" . blog_clean_inline($d['text'] ?? '') . "</h{$level}>";
                    break;
                case 'paragraph':
                    $html .= '<p class="text-gray-600 text-[17px] leading-[1.9] mb-6">' . blog_clean_inline($d['text'] ?? '') . '</p>';
                    break;
                case 'list':
                    $tag = (($d['style'] ?? 'unordered') === 'ordered') ? 'ol' : 'ul';
                    $listcls = $tag === 'ol' ? 'list-decimal' : 'list-disc';
                    $html .= "<{$tag} class=\"{$listcls} pl-6 mb-6 space-y-2 text-gray-600 text-[17px] leading-[1.8]\">";
                    foreach (($d['items'] ?? []) as $item) {
                        // Editor.js nested list items can be arrays; support both.
                        $content = is_array($item) ? ($item['content'] ?? '') : $item;
                        $html .= '<li>' . blog_clean_inline($content) . '</li>';
                    }
                    $html .= "</{$tag}>";
                    break;
                case 'quote':
                    $html .= '<blockquote class="border-l-4 border-[#c2410c] pl-6 py-2 my-8 italic text-gray-700 text-[19px]">'
                        . blog_clean_inline($d['text'] ?? '');
                    if (!empty($d['caption'])) {
                        $html .= '<footer class="text-[14px] not-italic text-gray-400 mt-2">— ' . blog_clean_inline($d['caption']) . '</footer>';
                    }
                    $html .= '</blockquote>';
                    break;
                case 'image':
                    $url = $d['file']['url'] ?? ($d['url'] ?? '');
                    if ($url) {
                        $url = blog_resolve_url($url);
                        $caption = $d['caption'] ?? '';
                        $html .= '<figure class="my-10">'
                            . '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars(strip_tags($caption)) . '" class="w-full rounded-[20px] shadow-[0_20px_40px_rgba(0,0,0,0.08)]" loading="lazy">';
                        if ($caption !== '') {
                            $html .= '<figcaption class="text-center text-gray-400 text-[14px] mt-3">' . blog_clean_inline($caption) . '</figcaption>';
                        }
                        $html .= '</figure>';
                    }
                    break;
                case 'delimiter':
                    $html .= '<div class="text-center text-gray-300 text-2xl my-10 tracking-[0.5em]">* * *</div>';
                    break;
                default:
                    // Unknown block: render its text if present, escaped.
                    if (!empty($d['text'])) {
                        $html .= '<p class="text-gray-600 text-[17px] leading-[1.9] mb-6">' . blog_clean_inline($d['text']) . '</p>';
                    }
            }
        }
        return $html;
    }
}

if (!function_exists('blog_clean_inline')) {
    /**
     * Allow only Editor.js inline formatting tags, strip everything else (XSS guard).
     */
    function blog_clean_inline($text) {
        return strip_tags($text, '<b><strong><i><em><u><a><mark><code><br>');
    }
}
