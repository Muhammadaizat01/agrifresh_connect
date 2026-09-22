<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "AgriFresh Connect | The Fresh Store for Kedah Smallholders";
$activePage = 'store';

// Query categories
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $catStmt->fetchAll();

// Query products with latest items first
$selectedCat = $_GET['cat'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, f.farm_name, f.farmer_name, f.avatar as farmer_avatar, f.location as farm_loc 
        FROM products p 
        LEFT JOIN (
            SELECT fm.id, fm.farm_name, u.name as farmer_name, fm.avatar, fm.farm_location_details as location 
            FROM farmers fm 
            JOIN users u ON fm.user_id = u.id
        ) f ON p.farmer_id = f.id 
        WHERE p.approval_status != 'rejected'";
$params = [];

if ($selectedCat !== 'all') {
    $sql .= " AND p.category_id = (SELECT id FROM categories WHERE slug = ? LIMIT 1)";
    $params[] = $selectedCat;
}
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.name_ms LIKE ? OR p.batch_id LIKE ? OR p.farm_location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Order with newest published harvests first!
$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Query spotlight products
$spotlightStmt = $pdo->query("SELECT p.*, f.farm_name, f.farmer_name, f.avatar as farmer_avatar, f.location as farm_loc 
                               FROM products p 
                               LEFT JOIN (
                                   SELECT fm.id, fm.farm_name, u.name as farmer_name, fm.avatar, fm.farm_location_details as location 
                                   FROM farmers fm 
                                   JOIN users u ON fm.user_id = u.id
                               ) f ON p.farmer_id = f.id 
                               WHERE p.is_spotlight = 1 ORDER BY p.id ASC");
$spotlights = $spotlightStmt->fetchAll();

// If no spotlight set, take latest 4 products
if (empty($spotlights)) {
    $spotlights = array_slice($products, 0, 4);
}

// Query price index
$priceStmt = $pdo->query("SELECT * FROM price_index ORDER BY id ASC");
$priceIndex = $priceStmt->fetchAll();

// Query farmers
$farmerStmt = $pdo->query("SELECT f.*, u.name as farmer_name, u.phone, u.email 
                           FROM farmers f 
                           JOIN users u ON f.user_id = u.id 
                           ORDER BY f.id ASC");
$farmers = $farmerStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Header Section -->
<section class="hero-section">
    <div class="container-custom hero-flex">
        <div>
            <h1 class="hero-heading">
                <span class="text-emerald"><?= $txt['heroHeading'] ?></span> 
                <span class="hero-subhead"><?= $txt['heroSubheading'] ?></span>
            </h1>
            <p class="hero-desc"><?= $txt['heroDesc'] ?></p>
        </div>

        <div class="hero-side-cards">
            <div class="side-card">
                <div class="side-card-icon bg-emerald-soft">
                    <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div class="card-head"><?= $txt['needHelp'] ?></div>
                    <a href="#farmers" class="card-link"><?= $txt['talkSpecialist'] ?> &rarr;</a>
                </div>
            </div>

            <div class="side-card">
                <div class="side-card-icon bg-amber-soft">
                    <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <div class="card-head"><?= $txt['visitHub'] ?></div>
                    <div class="card-sub"><?= $txt['hubLocation'] ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Spotlight Carousel Section -->
<section id="latest" class="container-custom" style="padding-top: 20px;">
    <div class="section-title-wrap">
        <h2 class="sec-title"><?= $txt['theLatest'] ?> <span class="sec-subtitle"><?= $txt['theLatestSub'] ?></span></h2>
    </div>

    <div class="carousel-track">
        <?php foreach ($spotlights as $item): ?>
            <?php 
                $name = $lang === 'en' ? $item['name'] : $item['name_ms'];
                $headline = ($lang === 'en' ? ($item['spotlight_headline'] ?: $item['description']) : ($item['spotlight_subtitle'] ?: $item['description_ms']));
            ?>
            <div class="spotlight-card">
                <div class="spotlight-header">
                    <span class="tag-badge">
                        <svg class="icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?= htmlspecialchars($item['tag'] ?: 'FRESH HARVEST') ?>
                    </span>
                    <h3 class="spotlight-title"><?= htmlspecialchars($name) ?></h3>
                    <p class="spotlight-tagline"><?= htmlspecialchars($headline) ?></p>

                    <div class="price-row">
                        <span class="price-main">RM <?= number_format($item['price_per_unit'], 2) ?></span>
                        <span class="price-unit">/<?= htmlspecialchars($item['unit']) ?></span>
                        <span class="price-cross">RM <?= number_format($item['middleman_price'], 2) ?></span>
                    </div>
                </div>

                <?php $itemSoldOut = (float)($item['quantity_available'] ?? 1) <= 0; ?>
                <div class="spotlight-img-wrap" style="position: relative;">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($name) ?>" style="<?= $itemSoldOut ? 'filter: grayscale(35%);' : '' ?>">
                    <?php if ($itemSoldOut): ?>
                        <div style="position: absolute; top: 12px; left: 12px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; box-shadow: 0 2px 8px rgba(220,38,38,0.5); z-index: 5;">🔴 SOLD OUT</div>
                    <?php endif; ?>
                    <div class="batch-floating-tag">Batch: <?= htmlspecialchars($item['batch_id']) ?></div>
                </div>

                <div class="spotlight-footer">
                    <button type="button" class="btn-secondary" onclick="openQRModalByBatch('<?= htmlspecialchars($item['batch_id'], ENT_QUOTES) ?>')">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span><?= $txt['traceFarm'] ?></span>
                    </button>

                    <?php if ($itemSoldOut): ?>
                        <button type="button" disabled style="background: #e5e7eb; color: #9ca3af; cursor: not-allowed; border: 1px solid #d1d5db; padding: 10px 16px; border-radius: 12px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <span>Sold Out</span>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-primary" onclick="addToCart({
                            id: <?= $item['id'] ?>,
                            name: '<?= addslashes($name) ?>',
                            price: <?= $item['price_per_unit'] ?>,
                            unit: '<?= addslashes($item['unit']) ?>',
                            batchId: '<?= $item['batch_id'] ?>',
                            image: '<?= addslashes($item['image_path']) ?>'
                        })">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span><?= $txt['addToBag'] ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Category Filter Pills & Product Grid -->
<section id="store" class="container-custom" style="padding-top: 30px;">
    <div class="category-pills-bar">
        <?php foreach ($categories as $c): ?>
            <?php 
                $cName = $lang === 'en' ? $c['name'] : $c['name_ms'];
                $isActive = $selectedCat === $c['slug'];
            ?>
            <a href="?cat=<?= $c['slug'] ?>#store" class="cat-pill <?= $isActive ? 'active' : '' ?>">
                <span><?= $c['icon'] ?></span>
                <span><?= htmlspecialchars($cName) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 24px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 class="sec-title"><?= $txt['exploreAll'] ?></h2>
            <p style="font-size: 13px; color: #6b7280;"><?= count($products) ?> fresh produce items loaded live from MySQL (Newest harvest first)</p>
        </div>
        <div>
            <form method="GET" action="index.php#store" style="display: flex; gap: 8px;">
                <input type="hidden" name="cat" value="<?= htmlspecialchars($selectedCat) ?>">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search produce, batch, or farm..." class="form-input" style="width: 240px;">
                <button type="submit" class="btn-dark">Search</button>
            </form>
        </div>
    </div>

    <div class="products-grid">
        <?php foreach ($products as $p): ?>
            <?php 
                $pName = $lang === 'en' ? $p['name'] : $p['name_ms'];
                $pDesc = $lang === 'en' ? $p['description'] : $p['description_ms'];
                $savePct = round((($p['middleman_price'] - $p['price_per_unit']) / $p['middleman_price']) * 100);
                $pSoldOut = (float)($p['quantity_available'] ?? 1) <= 0;
            ?>
            <div class="prod-card">
                <div class="prod-img-wrap" style="position: relative;">
                    <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($pName) ?>" style="<?= $pSoldOut ? 'filter: grayscale(35%);' : '' ?>">
                    <?php if ($pSoldOut): ?>
                        <div style="position: absolute; top: 12px; left: 12px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; box-shadow: 0 2px 8px rgba(220,38,38,0.5); z-index: 5;">🔴 SOLD OUT</div>
                    <?php endif; ?>
                    <div class="location-badge" style="<?= $pSoldOut ? 'top: 40px;' : '' ?>">📍 <?= htmlspecialchars(explode(',', $p['farm_location'])[0]) ?></div>
                    <div class="grade-badge"><?= htmlspecialchars($p['grade']) ?></div>
                </div>

                <div class="prod-body">
                    <div>
                        <div class="prod-status-line">🛡️ <?= htmlspecialchars(explode('(', $p['pesticide_status'])[0]) ?></div>
                        <h3 class="prod-name"><?= htmlspecialchars($pName) ?></h3>
                        <p class="prod-desc"><?= htmlspecialchars($pDesc) ?></p>
                    </div>

                    <div class="prod-price-box">
                        <div>
                            <span class="prod-price">RM <?= number_format($p['price_per_unit'], 2) ?></span>
                            <span style="font-size: 11px; color: #6b7280;">/<?= htmlspecialchars($p['unit']) ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11px; color: #9ca3af; text-decoration: line-through; display: block;">RM <?= number_format($p['middleman_price'], 2) ?></span>
                            <span class="prod-save">Save <?= $savePct ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="prod-actions">
                    <button type="button" class="btn-secondary" onclick="openQRModalByBatch('<?= htmlspecialchars($p['batch_id'], ENT_QUOTES) ?>')">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Trace QR</span>
                    </button>

                    <?php if ($pSoldOut): ?>
                        <button type="button" disabled style="background: #e5e7eb; color: #9ca3af; cursor: not-allowed; border: 1px solid #d1d5db; padding: 10px 16px; border-radius: 12px; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <span>Sold Out</span>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-primary" onclick="addToCart({
                            id: <?= $p['id'] ?>,
                            name: '<?= addslashes($pName) ?>',
                            price: <?= $p['price_per_unit'] ?>,
                            unit: '<?= addslashes($p['unit']) ?>',
                            batchId: '<?= $p['batch_id'] ?>',
                            image: '<?= addslashes($p['image_path']) ?>'
                        })">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>Add to Bag</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Fair Trade Impact & Value Cards -->
<section id="impact" class="container-custom" style="padding-top: 60px;">
    <div class="text-center" style="max-width: 650px; margin: 0 auto 30px;">
        <span class="badge-pill">Direct Fair Trade Model</span>
        <h2 class="sec-title" style="margin-top: 10px;"><?= $txt['whyAgriFresh'] ?></h2>
        <p class="sec-desc" style="font-size: 14px; margin-top: 6px;"><?= $txt['whyAgriFreshSub'] ?></p>
    </div>

    <div class="value-cards-grid">
        <div class="val-card">
            <div class="val-icon" style="background:#ecfdf5; color:#059669;">🔍</div>
            <h3><?= $txt['card1Title'] ?></h3>
            <p><?= $txt['card1Desc'] ?></p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#eff6ff; color:#2563eb;">⚖️</div>
            <h3><?= $txt['card2Title'] ?></h3>
            <p><?= $txt['card2Desc'] ?></p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#fef3c7; color:#d97706;">⭐</div>
            <h3><?= $txt['card3Title'] ?></h3>
            <p><?= $txt['card3Desc'] ?></p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#f5f3ff; color:#7c3aed;">🚚</div>
            <h3><?= $txt['card4Title'] ?></h3>
            <p><?= $txt['card4Desc'] ?></p>
        </div>
    </div>

    <!-- Live Price Index from MySQL -->
    <div class="table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800;"><?= $txt['transparencyTitle'] ?></h3>
                <p style="font-size: 12px; color: #6b7280;"><?= $txt['transparencySubtitle'] ?></p>
            </div>
            <div style="font-size: 11px; font-weight: 700; color: #065f46; background: #ecfdf5; padding: 6px 12px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                Live MySQL Sync @ Famox Lunas Hub
            </div>
        </div>

        <table class="table-custom">
            <thead>
                <tr>
                    <th>Crop Name</th>
                    <th>AgriFresh Direct (RM/kg)</th>
                    <th>Market Middleman Price</th>
                    <th>Farmer Income Gain</th>
                    <th>Market Trend</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($priceIndex as $idx): ?>
                    <tr>
                        <td style="font-weight: 800; color: #111827;"><?= htmlspecialchars($idx['crop_name']) ?></td>
                        <td style="font-weight: 900; color: #059669;">RM <?= number_format($idx['direct_price'], 2) ?></td>
                        <td style="color: #9ca3af; text-decoration: line-through;">RM <?= number_format($idx['middleman_price'], 2) ?></td>
                        <td>
                            <span style="background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 9999px;">
                                <?= htmlspecialchars($idx['farmer_gain']) ?>
                            </span>
                        </td>
                        <td class="font-mono"><?= htmlspecialchars($idx['trend']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Meet the Farmers of Kedah -->
<section id="farmers" class="container-custom" style="padding-top: 40px;">
    <div class="text-center" style="max-width: 600px; margin: 0 auto 30px;">
        <span class="badge-pill">Local Producers</span>
        <h2 class="sec-title" style="margin-top: 10px;">Meet the Farmers of Kedah</h2>
        <p class="sec-desc" style="font-size: 13px; margin-top: 4px;">Smallholder producers supplying Famox Enterprise aggregation hub in Lunas.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
        <?php foreach ($farmers as $f): ?>
            <div style="background: #ffffff; border-radius: 24px; padding: 24px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="text-align: center;">
                    <img src="<?= htmlspecialchars($f['avatar']) ?>" alt="<?= htmlspecialchars($f['farmer_name']) ?>" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; margin: 0 auto 12px; display: block;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 6px;">
                        <?= htmlspecialchars($f['famox_tier']) ?>
                    </span>
                    <h3 style="font-size: 16px; font-weight: 800; margin-top: 8px;"><?= htmlspecialchars($f['farmer_name']) ?></h3>
                    <p style="font-size: 12px; color: #059669; font-weight: 600;"><?= htmlspecialchars($f['farm_name']) ?></p>
                    <p style="font-size: 11px; color: #6b7280; margin-top: 2px;">📍 <?= htmlspecialchars($f['farm_location_details']) ?></p>
                    <p style="font-size: 11px; font-style: italic; color: #4b5563; background: #f9fafb; padding: 10px; border-radius: 12px; margin-top: 12px; border: 1px solid #f3f4f6;">
                        "<?= htmlspecialchars($f['quote']) ?>"
                    </p>
                </div>
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6; font-size: 11px; display: flex; justify-content: space-between;">
                    <span>Cert: <strong><?= htmlspecialchars($f['farming_certification']) ?></strong></span>
                    <span>Rating: <strong>★ <?= number_format($f['rating'], 1) ?></strong></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Famox Enterprise Partnership Banner -->
<section id="famox" class="container-custom" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="famox-banner">
        <img src="assets/img/bg_marketplace.jpg" alt="Famox Harvest Farmland" class="banner-bg-img">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <span class="badge-pill" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #34d399;">Famox Enterprise Anchor Partnership</span>
            <h2 class="banner-title" style="margin-top: 12px;">Empowering Kedah Smallholders Through Traceable Digital Trade.</h2>
            <p class="banner-desc">Based in Lunas, Kedah, Famox Enterprise Sdn Bhd acts as the aggregation, packaging, and quality certification hub for over 200 smallholder vegetable and fruit growers. AgriFresh Connect links these farms directly with schools, restaurants, and consumers.</p>
            
            <div class="banner-stats">
                <div>
                    <div class="stat-num">200+</div>
                    <div class="stat-lbl">Smallholder Farms</div>
                </div>
                <div>
                    <div class="stat-num">100%</div>
                    <div class="stat-lbl">MyGAP Traceable</div>
                </div>
                <div>
                    <div class="stat-num">&lt; 24h</div>
                    <div class="stat-lbl">Harvest to Door</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
