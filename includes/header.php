<?php
require_once __DIR__ . '/../config/db.php';
global $pageTitle, $activePage, $lang, $txt;

function shortName(string $name, int $wordLimit = 1): string {
    $parts = preg_split('/\s+/', trim($name));
    return implode(' ', array_slice($parts, 0, $wordLimit));
}

$user = currentUser();

$userRole = '';
if ($user) {

    $rawRole = $user['role'] ?? $user['user_role'] ?? $user['type'] ?? $user['role_id'] ?? '';

    if ($rawRole === 1 || $rawRole === '1') $rawRole = 'admin';
    if ($rawRole === 2 || $rawRole === '2') $rawRole = 'farmer';
    if ($rawRole === 3 || $rawRole === '3') $rawRole = 'buyer';

    $userRole = strtolower(trim((string)$rawRole));
}

$dashboardUrl = 'farmer_dashboard.php';
if ($user) {
    if (str_contains($userRole, 'admin')) {
        $dashboardUrl = 'admin_panel.php';
    } elseif (str_contains($userRole, 'buyer')) {
        $dashboardUrl = 'buyer_dashboard.php';
    } else {
        $dashboardUrl = 'farmer_dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'AgriFresh Connect | The Fresh Store for Kedah Smallholders') ?></title>
    <link rel="icon" type="image/png" href="assets/img/agrifresh_logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/qrcode.min.js"></script>
</head>
<body>
<?php include __DIR__ . '/promo_ribbon.php'; ?>

<header class="glass-header">
    <div class="container-custom header-inner">

        <a href="index.php" class="brand-logo-wrap">
            <div class="brand-logo-img">
                <img src="assets/img/agrifresh_logo.png" alt="AgriFresh Logo">
            </div>
            <div class="brand-text">
                <span class="brand-main">AgriFresh</span>
                <span class="brand-sub">CONNECT</span>
            </div>
        </a>

        <nav class="nav-links">
            <a href="index.php" class="nav-link <?= ($activePage ?? '') === 'store' ? 'active' : '' ?>"><?= $txt['store'] ?? 'Store' ?></a>
            <a href="index.php#latest" class="nav-link"><?= $txt['theLatest'] ?? 'The latest' ?></a>
            <a href="index.php#farmers" class="nav-link"><?= $txt['farmers'] ?? 'Kedah Farmers' ?></a>
            <a href="index.php#traceability" class="nav-link"><?= $txt['qrTraceability'] ?? 'QR Traceability' ?></a>
            <a href="index.php#impact" class="nav-link"><?= $txt['fairTradeImpact'] ?? 'Fair Trade Impact' ?></a>
            <a href="index.php#famox" class="nav-link"><?= $txt['famoxHub'] ?? 'Famox Hub Lunas' ?></a>
        </nav>

        <div class="header-actions">

            <button type="button" onclick="openScannerModal()" class="btn-scan-pill">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span><?= $txt['scanQR'] ?? 'Scan QR' ?></span>
            </button>

            <a href="?lang=<?= ($lang ?? 'en') === 'en' ? 'ms' : 'en' ?>" class="lang-toggle-btn">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span><?= strtoupper($lang ?? 'en') ?></span>
            </a>

            <?php if ($user): ?>

    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">

        <a href="<?= htmlspecialchars($dashboardUrl) ?>"
           class="btn-header-user"
           title="Open Dashboard"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #ecfdf5; border: 1.5px solid #10b981; border-radius: 9999px; color: #065f46; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">

            <span title="<?= htmlspecialchars($user['name']) ?>">👤 <?= htmlspecialchars(shortName($user['name'])) ?></span>
            <span class="user-arrow" style="font-weight: 800; color: #059669;">&rarr;</span>
        </a>

    </div>

<?php else: ?>

    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">

        <a href="login.php" class="btn-header-auth" style="white-space: nowrap;">Sign In</a>
        <a href="register.php" class="btn-header-outline" style="white-space: nowrap;">Register</a>

    </div>

<?php endif; ?>

<div class="dropdown-wrap">
    <button type="button" class="btn-role-pill" onclick="toggleRoleMenu(event)">
        <span>● Role Access</span>

        <svg class="icon-xs"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>
                    <div id="roleDropdownMenu" class="dropdown-menu">
                    <div class="dropdown-title">System Portals</div>

                    <a href="index.php" class="dropdown-item">
                        <span>🛒</span>
                        <div>
                            <strong>Buyer Storefront</strong>
                            <div style="font-size: 10px; color: #6b7280;">Browse & Buy Kedah Produce</div>
                        </div>
                    </a>

                    <a href="buyer_dashboard.php" class="dropdown-item">
                        <span>📦</span>
                        <div>
                            <strong>Buyer Dashboard</strong>
                            <div style="font-size: 10px; color: #6b7280;">Track Orders, 4-Stage Delivery & Profile</div>
                        </div>
                    </a>

                    <a href="farmer_dashboard.php" class="dropdown-item">
                        <span>👨‍🌾</span>
                        <div>
                            <strong>Farmer Portal (Famox)</strong>
                            <div style="font-size: 10px; color: #6b7280;">Add, Edit & Remove Produce</div>
                        </div>
                    </a>

                    <a href="admin_panel.php" class="dropdown-item">
                        <span>🏢</span>
                        <div>
                            <strong>Famox Admin Panel</strong>
                            <div style="font-size: 10px; color: #6b7280;">Hub & Quality Oversight</div>
                        </div>
                    </a>

                    <hr style="margin: 4px 0; border: 0; border-top: 1px solid #f3f4f6;">

                    <?php if ($user): ?>
                        <a href="logout.php" class="dropdown-item" style="color: #dc2626;">
                            <span>🚪</span>
                            <div>
                            <strong title="<?= htmlspecialchars($user['name']) ?>">Sign Out (<?= htmlspecialchars(shortName($user['name'])) ?>)</strong>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="dropdown-item">
                            <span>🔑</span>
                            <div>
                                <strong>Sign In</strong>
                                <div style="font-size: 10px; color: #6b7280;">1-Click Demo or Password</div>
                            </div>
                        </a>
                        <a href="register.php" class="dropdown-item">
                            <span>📝</span>
                            <div>
                                <strong>Register</strong>
                                <div style="font-size: 10px; color: #6b7280;">Register Buyer / Farmer</div>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <button type="button" onclick="openCartDrawer()" class="btn-bag" aria-label="Cart Bag">
                <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span id="headerCartCount" class="bag-badge">0</span>
            </button>
        </div>
    </div>

    <script>
    function toggleRoleMenu(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var menu = document.getElementById('roleDropdownMenu');
        if (!menu) return;
        var isHidden = window.getComputedStyle(menu).display === 'none';
        if (isHidden) {
            menu.classList.add('show');
            menu.style.setProperty('display', 'block', 'important');
        } else {
            menu.classList.remove('show');
            menu.style.setProperty('display', 'none', 'important');
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-wrap')) {
            var menu = document.getElementById('roleDropdownMenu');
            if (menu) {
                menu.classList.remove('show');
                menu.style.setProperty('display', 'none', 'important');
            }
        }
    });
    </script>
</header>
<main>
