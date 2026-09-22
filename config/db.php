<?php
// AgriFresh Connect Database Configuration & Helpers
// FYP: Muhammad Aizat Izzuddin Bin Azmi (B23101069) - AIMST University
// Partner: Famox Enterprise Sdn Bhd, Lunas Kedah

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_conn = getenv('DB_CONNECTION') ?: 'mysql';
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: ($db_conn === 'pgsql' ? '5432' : '3306');
$db_name = getenv('DB_DATABASE') ?: 'agrifresh_connect';
$db_user = getenv('DB_USERNAME') ?: 'root';
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

try {
    if ($db_conn === 'pgsql') {
        $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name};sslmode=require";
    } else {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    }
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:12px;margin:20px;'><strong>Database Connection Notice:</strong> " . $e->getMessage() . "<br>Please ensure database credentials and host are configured properly in environment variables or phpMyAdmin.</div>");
}

// Language Handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = in_array($_GET['lang'], ['en', 'ms']) ? $_GET['lang'] : 'en';
}
$lang = $_SESSION['lang'] ?? 'en';

// Auth Helpers
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireRole($roleSlug) {
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if ($_SESSION['user']['role_slug'] !== $roleSlug && $_SESSION['user']['role_slug'] !== 'admin') {
        die("<div style='font-family:sans-serif;padding:30px;text-align:center;'><h2>Access Denied</h2><p>You need " . htmlspecialchars($roleSlug) . " privileges to view this page.</p><a href='index.php'>Return to Store</a></div>");
    }
}

// Translations Dictionary
$t = [
    'en' => [
        'brand' => 'AgriFresh Connect',
        'tagline' => 'Scan • Connect • Grow Together',
        'store' => 'Store',
        'theLatest' => 'The latest.',
        'theLatestSub' => 'Fresh morning harvests plucked at dawn.',
        'farmers' => 'Kedah Farmers',
        'qrTraceability' => 'QR Traceability',
        'fairTradeImpact' => 'Fair Trade Impact',
        'famoxHub' => 'Famox Hub Lunas',
        'switchView' => 'Role Navigation',
        'buyerStore' => 'Buyer Storefront',
        'farmerPortal' => 'Farmer Portal (Famox)',
        'adminPanel' => 'Famox Admin Panel',
        'signIn' => 'Sign In',
        'register' => 'Register',
        'signOut' => 'Sign Out',
        'myProfile' => 'My Profile',
        'bag' => 'Bag',
        'heroHeading' => 'Store.',
        'heroSubheading' => 'The freshest way to buy produce straight from Kedah farmers.',
        'heroDesc' => 'Direct from smallholders in Lunas, Kulim, and Baling. Packed at the Famox aggregation hub with full QR harvest traceability.',
        'needHelp' => 'Need farm sourcing advice?',
        'talkSpecialist' => 'Talk to a Famox Specialist',
        'visitHub' => 'Visit Famox Central Hub',
        'hubLocation' => 'Lunas & Kulim, Kedah Darul Aman',
        'exploreAll' => 'Explore all harvests.',
        'traceFarm' => 'Trace Farm & QR',
        'addToBag' => 'Add to Bag',
        'added' => 'Added to Bag',
        'whyAgriFresh' => 'The AgriFresh Difference.',
        'whyAgriFreshSub' => 'Why buying directly from Famox smallholders is better for everyone.',
        'card1Title' => '100% QR Traceability',
        'card1Desc' => 'Scan the physical QR code on any crate to see harvest time, GPS farm soil, and pesticide test logs.',
        'card2Title' => 'Direct Fair Pricing',
        'card2Desc' => 'Kedah smallholders earn up to 48% more income by eliminating exploitative middleman cuts.',
        'card3Title' => 'Famox Quality Grading',
        'card3Desc' => 'Every harvest is inspected, temperature-checked, and graded Grade-A at the Lunas aggregation hub.',
        'card4Title' => '24-Hour Farm-to-Table',
        'card4Desc' => 'From morning harvest in Lunas & Kulim to restaurants, schools, and tables in under 24 hours.',
        'transparencyTitle' => 'Transparent Fair Trade Index',
        'transparencySubtitle' => 'Real-time comparison between conventional middleman pricing and AgriFresh Direct.',
        'scanTitle' => 'Live Farm-to-Table QR Scanner',
        'scanSub' => 'Scan produce crates or enter a Famox batch code to see authentic harvest origin.'
    ],
    'ms' => [
        'brand' => 'AgriFresh Connect',
        'tagline' => 'Imbas • Hubung • Maju Bersama',
        'store' => 'Kedai Segar',
        'theLatest' => 'Terkini.',
        'theLatestSub' => 'Hasil tuaian pagi segar dipetik waktu subuh.',
        'farmers' => 'Pekebun Kedah',
        'qrTraceability' => 'Ketulenan QR',
        'fairTradeImpact' => 'Impak Harga Adil',
        'famoxHub' => 'Hab Famox Lunas',
        'switchView' => 'Navigasi Peranan',
        'buyerStore' => 'Kedai Pembeli',
        'farmerPortal' => 'Portal Pekebun (Famox)',
        'adminPanel' => 'Panel Pentadbir Famox',
        'signIn' => 'Log Masuk',
        'register' => 'Daftar Akaun',
        'signOut' => 'Log Keluar',
        'myProfile' => 'Profil Saya',
        'bag' => 'Troli',
        'heroHeading' => 'Kedai.',
        'heroSubheading' => 'Cara paling segar membeli hasil tanaman terus dari pekebun Kedah.',
        'heroDesc' => 'Terus dari pekebun kecil Lunas, Kulim dan Baling. Dihimpunkan di hab Famox dengan kebolehkesanan kod QR waktu tuai.',
        'needHelp' => 'Perlukan nasihat bekalan?',
        'talkSpecialist' => 'Sembang bersama Pakar Famox',
        'visitHub' => 'Lawati Hab Pusat Famox',
        'hubLocation' => 'Lunas & Kulim, Kedah Darul Aman',
        'exploreAll' => 'Terokai semua hasil tuaian.',
        'traceFarm' => 'Jejak Ladang & QR',
        'addToBag' => 'Tambah ke Troli',
        'added' => 'Ditambah ke Troli',
        'whyAgriFresh' => 'Kelebihan AgriFresh.',
        'whyAgriFreshSub' => 'Mengapa membeli terus dari pekebun Famox lebih menguntungkan semua.',
        'card1Title' => '100% Kebolehkesanan QR',
        'card1Desc' => 'Imbas kod QR pada setiap bakul untuk melihat waktu tuai, koordinat ladang dan laporan ujian makmal.',
        'card2Title' => 'Harga Adil Terus ke Pekebun',
        'card2Desc' => 'Pekebun kecil Kedah menikmati peningkatan pendapatan sehingga 48% tanpa potongan orang tengah.',
        'card3Title' => 'Penggredan Kualiti Famox',
        'card3Desc' => 'Setiap tuaian diperiksa kualiti, suhu dan digred Gred A di hab pengumpulan Lunas.',
        'card4Title' => 'Dari Ladang ke Meja Dalam 24 Jam',
        'card4Desc' => 'Dari tuaian pagi di Lunas & Kulim ke restoran, sekolah dan rumah sekitar Kedah & Pulau Pinang kurang 24 jam.',
        'transparencyTitle' => 'Indeks Ketelusan Harga Pasaran',
        'transparencySubtitle' => 'Perbandingan masa nyata antara harga orang tengah dan harga terus AgriFresh.',
        'scanTitle' => 'Pengimbas QR Ladang-ke-Meja',
        'scanSub' => 'Imbas bakul sayur atau masukkan kod kelompok Famox untuk semak asal-usul tuaian.'
    ]
];

$txt = $t[$lang];
?>