<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$batch = trim($_GET['batch'] ?? '');

if ($batch === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Batch ID required'
    ]);
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
        u.name AS farmer_name
    FROM products p
    LEFT JOIN farmers fm
        ON p.farmer_id = fm.id
    LEFT JOIN users u
        ON fm.user_id = u.id
    WHERE p.batch_id = ?
    LIMIT 1
");

$stmt->execute([$batch]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prod) {
    echo json_encode([
        'success' => false,
        'message' => 'Batch not found in MySQL database'
    ]);
    exit;
}

$farmerName = trim($prod['farmer_name'] ?? '');

if ($farmerName === '') {
    $farmerName = 'Unknown Farmer';
}

$farmName = trim($prod['farm_name'] ?? '');

if ($farmName === '') {
    $farmName = 'Farm information unavailable';
}

$location = trim($prod['farm_loc'] ?? '');

if ($location === '') {
    $location = trim($prod['farm_location'] ?? '');
}

if ($location === '') {
    $location = 'Kedah';
}

$farmerAvatar = trim($prod['farmer_avatar'] ?? '');

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
$certNumber = trim($prod['cert_number'] ?? '');
if ($certNumber === '') {
    $certNumber = 'Not available';
}

$productImage = trim($prod['image_path'] ?? '');

echo json_encode(
    [
        'success' => true,
        'batchId' => $prod['batch_id'],
        'name' => $prod['name'],
        'nameMs' => $prod['name_ms'] ?? '',
        'price' => $prod['price_per_unit'],
        'unit' => $prod['unit'],
        'farmerId' => $prod['farmer_id'],
        'farmerName' => $farmerName,
        'farmerAvatar' => $farmerAvatarUrl,
        'farmName' => $farmName,
        'location' => $location,
        'harvestDate' => $prod['harvest_date'],
        'grade' => $prod['grade'],
        'pesticide' => $prod['pesticide_status'],
        'storage' => $prod['storage_temp'],
        'cert' => $certNumber,
        'image' => $productImage
    ],

    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE
);

exit;
?>