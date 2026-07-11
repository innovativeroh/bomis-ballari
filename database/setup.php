<?php
/**
 * Unified Database Setup and Blog Seeder Script.
 * - Creates the database if it doesn't exist.
 * - Creates all tables: admins, contact_forms, enquiry_forms, newsletter_subscribers, activity_logs, blogs.
 * - Creates default admin user.
 * - Parses and seeds static blog posts from blogs/*.html.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/blog_helpers.php';

// Check if running in CLI or browser
$is_cli = (php_sapi_name() === 'cli');
$nl = $is_cli ? "\n" : "<br>";

echo "Connecting to MySQL server..." . $nl;
$conn = get_db_connection(false);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . $nl);
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Database '" . DB_NAME . "' ready." . $nl;
} else {
    die("Error creating database: " . $conn->error . $nl);
}
$conn->close();

// Reconnect to the database
$conn = get_db_connection(true);
if (!$conn) {
    die("Failed to select database '" . DB_NAME . "'." . $nl);
}

echo "Creating tables..." . $nl;

// Create admins table
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create contact_forms table
$conn->query("CREATE TABLE IF NOT EXISTS contact_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    child_age VARCHAR(50),
    program VARCHAR(100),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create enquiry_forms table
$conn->query("CREATE TABLE IF NOT EXISTS enquiry_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_name VARCHAR(100),
    child_name VARCHAR(100),
    child_age VARCHAR(50),
    program VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create newsletter_subscribers table
$conn->query("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create activity_logs table
$conn->query("CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255),
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
)");

// Create blogs table
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

echo "Tables ready." . $nl;

// Insert default admin
$admin_email = 'admin@bomis-ballari.com';
$admin_pass = password_hash('Bomis@123456', PASSWORD_DEFAULT);
$admin_name = 'Admin';

$check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
$check->bind_param("s", $admin_email);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $admin_name, $admin_email, $admin_pass);
    $stmt->execute();
    echo "Default admin created successfully." . $nl;
} else {
    echo "Admin already exists." . $nl;
}

// Seed blogs
echo "Seeding blogs..." . $nl;

$seed = [
    ['file' => 'pre-school-admission-ballari.html',
     'excerpt' => 'A complete, honest guide to choosing the right nursery or pre-school in Ballari — covering age, what to look for, and questions to ask.',
     'cover' => 'assets/blogs/preschool_admission.png',
     'date' => '2026-03-25 09:00:00'],
    ['file' => 'smart-classrooms-sports-safety.html',
     'excerpt' => 'A thoughtful look at what holistic education actually means — and how BOMIS Ballari delivers it through facilities, curriculum, and values that go far beyond textbooks.',
     'cover' => 'assets/blogs/sports_fitness.png',
     'date' => '2026-03-25 09:05:00'],
    ['file' => 'cbse-school-ballari-investment.html',
     'excerpt' => 'A practical guide for parents in Ballari to understand why CBSE education can shape a stronger, future-ready child and build a strong foundation.',
     'cover' => 'assets/blogs/cbse_investment.png',
     'date' => '2026-03-25 09:10:00'],
    ['file' => 'building-leadership-skills-young-age.html',
     'excerpt' => "Leadership isn't a trait you're born with — it's a skill built over time. Discover why schools play a critical role in developing young leaders.",
     'cover' => null,
     'date' => '2026-03-25 09:15:00'],
];

/** Parse a static blog file: pull title + .content-area h2/h3/p/ul into Editor.js blocks. */
function extract_blocks($path) {
    $html = file_get_contents($path);
    if ($html === false) return [null, null];

    $title = 'Untitled';
    if (preg_match('/<title>([^<]*)<\/title>/i', $html, $m)) {
        $title = trim(preg_split('/\s+[-|]\s+/', html_entity_decode($m[1]))[0]);
    }

    $blocks = [];
    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_use_internal_errors($prev);
    $xpath = new DOMXPath($doc);

    $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' content-area ')]//*[self::h2 or self::h3 or self::p or self::ul or self::ol]");
    foreach ($nodes as $node) {
        $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
        if ($text === '') continue;
        $tag = strtolower($node->nodeName);
        if ($tag === 'h2' || $tag === 'h3') {
            $blocks[] = ['type' => 'header', 'data' => ['text' => htmlspecialchars($text), 'level' => $tag === 'h2' ? 2 : 3]];
        } elseif ($tag === 'p') {
            $blocks[] = ['type' => 'paragraph', 'data' => ['text' => htmlspecialchars($text)]];
        } else {
            $items = [];
            foreach ($node->getElementsByTagName('li') as $li) {
                $li_text = trim(preg_replace('/\s+/', ' ', $li->textContent));
                if ($li_text !== '') $items[] = htmlspecialchars($li_text);
            }
            if ($items) {
                $blocks[] = ['type' => 'list', 'data' => ['style' => $tag === 'ol' ? 'ordered' : 'unordered', 'items' => $items]];
            }
        }
    }
    return [$title, $blocks];
}

$blogs_dir = __DIR__ . '/../blogs/';
foreach ($seed as $s) {
    $path = $blogs_dir . $s['file'];
    if (!file_exists($path)) {
        echo "Skip (missing file): {$s['file']}" . $nl;
        continue;
    }

    list($title, $blocks) = extract_blocks($path);
    if (!$title) {
        echo "Skip (parse failed): {$s['file']}" . $nl;
        continue;
    }

    // Check if already exists based on title
    $check = $conn->prepare("SELECT id FROM blogs WHERE title = ? LIMIT 1");
    $check->bind_param("s", $title);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "Exists (skipped): {$title}" . $nl;
        continue;
    }

    $slug = blog_unique_slug($conn, $title);
    $content = json_encode(['time' => time() * 1000, 'blocks' => $blocks, 'version' => '2.30.0']);
    $status = 'published';
    
    $stmt = $conn->prepare("INSERT INTO blogs (title, slug, excerpt, cover_image, content, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $slug, $s['excerpt'], $s['cover'], $content, $status, $s['date']);
    if ($stmt->execute()) {
        echo "Seeded: {$title} (" . count($blocks) . " blocks)" . $nl;
    } else {
        echo "Error seeding {$title}: " . $conn->error . $nl;
    }
}

echo $nl . "Database setup and blog seeding completed successfully!" . $nl;
$conn->close();
