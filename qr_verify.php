<?php
require_once __DIR__ . '/config/db.php';

$batchCode = trim($_GET['batch'] ?? '');
if ($batchCode === '') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        fm.id AS farmer_id,
        fm.farm_name,
        fm.avatar AS farmer_avatar,
        fm.farm_location_details AS farm_loc,
        fm.cert_number,
        fm.experience_years,
        u.name AS farmer_name
    FROM products p
    LEFT JOIN farmers fm
        ON p.farmer_id = fm.id
    LEFT JOIN users u
        ON fm.user_id = u.id
    WHERE p.batch_id = ?
    LIMIT 1
");

$stmt->execute([$batchCode]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Batch Not Found | AgriFresh Connect';
    $activePage = 'traceability';
    include __DIR__ . '/includes/header.php';
    ?>
    <div
        class="container-custom"
        style="
            padding: 80px 20px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        "
    >
        <div
            style="
                background: #ffffff;
                border-radius: 28px;
                padding: 50px 30px;
                border: 1px solid var(--border-subtle);
                box-shadow: var(--shadow-lg);
            "
        >
            <div
                style="
                    width: 60px;
                    height: 60px;
                    margin: 0 auto 18px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #fef2f2;
                    font-size: 28px;
                "
            >
                ⚠️
            </div>
            <h1
                style="
                    margin: 0 0 8px;
                    color: #111827;
                    font-size: 26px;
                    font-weight: 800;
                "
            >
                Batch Not Found
            </h1>
            <p
                style="
                    margin: 0 0 25px;
                    color: #6b7280;
                    font-size: 14px;
                "
            >
                We could not find batch
                <strong>
                    <?= htmlspecialchars($batchCode) ?>
                </strong>
                in the AgriFresh database.
            </p>
            <a
                href="index.php"
                class="btn-dark"
            >
                &larr; Return to Storefront
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$farmerName = trim($product['farmer_name'] ?? '');
if ($farmerName === '') {
    $farmerName = 'Unknown Farmer';
}

$farmName = trim($product['farm_name'] ?? '');
if ($farmName === '') {
    $farmName = 'Farm information unavailable';
}

$farmLocation = trim($product['farm_loc'] ?? '');
if ($farmLocation === '') {
    $farmLocation = trim($product['farm_location'] ?? '');
}
if ($farmLocation === '') {
    $farmLocation = 'Kedah';
}


$farmerAvatar = trim($product['farmer_avatar'] ?? '');

if ($farmerAvatar !== '') {
    if (
        str_starts_with($farmerAvatar, 'http://') ||
        str_starts_with($farmerAvatar, 'https://')
    ) {
        $farmerAvatarUrl = $farmerAvatar;
    } else {
        $farmerAvatarUrl = ltrim($farmerAvatar, '/');
    }
} else {
    $farmerAvatarUrl = 'assets/img/agrifresh_logo.png';
}

$productName = trim($product['name'] ?? 'Product');
$batchId = trim($product['batch_id'] ?? '');
$harvestDate = trim($product['harvest_date'] ?? '');
$grade = trim($product['grade'] ?? '');
$pesticideStatus = trim($product['pesticide_status'] ?? '');
$storageTemp = trim($product['storage_temp'] ?? '');
$certNumber = trim($product['cert_number'] ?? '');

if ($harvestDate === '') {
    $harvestDate = 'Not available';
}

if ($grade === '') {
    $grade = 'Not available';
}

if ($pesticideStatus === '') {
    $pesticideStatus = 'Not available';
}

if ($storageTemp === '') {
    $storageTemp = 'Not available';
}

if ($certNumber === '') {
    $certNumber = 'Not available';
}

$pageTitle =
    'Verify Batch ' .
    htmlspecialchars($batchId) .
    ' | AgriFresh Connect';
$activePage = 'traceability';

include __DIR__ . '/includes/header.php';
?>
<div
    class="container-custom"
    style="
        padding: 40px 1.25rem 80px;
        max-width: 800px;
    "
>
    <div
        style="
            background: #ffffff;
            border-radius: 32px;
            padding: 32px;
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-lg);
        "
    >
        <div
            style="
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 24px;
            "
        >

            <div
                style="
                    width: 52px;
                    height: 52px;
                    flex: 0 0 52px;
                    border-radius: 50%;
                    overflow: hidden;
                    border: 2px solid #10b981;
                "
            >
                <img
                    src="assets/img/agrifresh_logo.png"
                    alt="AgriFresh Connect"
                    style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    "
                >
            </div>

            <div>
                <span class="badge-pill">
                    Verified QR Harvest Passport
                </span>
                <h1
                    style="
                        font-size: 24px;
                        line-height: 1.25;
                        font-weight: 900;
                        color: #111827;
                        margin: 4px 0 0;
                    "
                >
                    <?= htmlspecialchars($productName) ?>
                </h1>
            </div>
        </div>

        <div
            class="qr-passport-grid"
            style="margin-bottom: 24px;"
        >
            <div class="qr-canvas-box">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=064e3b&data=<?= urlencode('https://agrifresh.my/verify/' . $batchId) ?>"
                    alt="QR Code"
                    style="
                        width: 150px;
                        height: 150px;
                        border-radius: 8px;
                    "
                >

                <div class="batch-code-tag">
                    BATCH:
                    <?= htmlspecialchars($batchId) ?>
                </div>
                <div class="verified-origin-pill">
                    ✓ Famox Verified Origin
                </div>
            </div>

            <div class="qr-details-box">
                <div class="farmer-mini-card">
                    <img
                        src="<?= htmlspecialchars($farmerAvatarUrl) ?>"
                        alt="<?= htmlspecialchars($farmerName) ?>"
                        class="farmer-thumb"
                        onerror="
                            this.onerror = null;
                            this.src = 'assets/img/agrifresh_logo.png';
                        "
                    >

                    <div>
                        <h4 class="farmer-name">
                            <?= htmlspecialchars($farmerName) ?>
                        </h4>
                        <p class="farm-name text-emerald">
                            <?= htmlspecialchars($farmName) ?>
                        </p>
                        <p class="farm-loc">
                            📍
                            <?= htmlspecialchars($farmLocation) ?>
                        </p>
                    </div>
                </div>

                <div class="provenance-rows">
                    <div class="prov-row">
                        <span class="prov-label">
                            Harvest Timestamp:
                        </span>
                        <span class="prov-val font-mono">
                            <?= htmlspecialchars($harvestDate) ?>
                        </span>
                    </div>

                    <div class="prov-row">
                        <span class="prov-label">
                            Quality Standard:
                        </span>
                        <span class="prov-val text-emerald font-bold">
                            <?= htmlspecialchars($grade) ?>
                        </span>
                    </div>

                    <div class="prov-row">
                        <span class="prov-label">
                            Pesticide Residue:
                        </span>
                        <span class="prov-val text-emerald">
                            <?= htmlspecialchars($pesticideStatus) ?>
                        </span>
                    </div>

                    <div class="prov-row">
                        <span class="prov-label">
                            Storage Temperature:
                        </span>
                        <span class="prov-val">
                            <?= htmlspecialchars($storageTemp) ?>
                        </span>
                    </div>

                    <div class="prov-row">
                        <span class="prov-label">
                            Certification #:
                        </span>
                        <span class="prov-val font-mono">
                            <?= htmlspecialchars($certNumber) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 12px;
            "
        >

            <a
                href="index.php"
                class="btn-dark"
            >
                &larr; Return to Storefront
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="btn-emerald"
            >
                🖨️ Print Crate QR Certificate
            </button>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/includes/footer.php';
?>