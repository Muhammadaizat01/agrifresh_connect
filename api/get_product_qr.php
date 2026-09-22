<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$batch = trim($_GET['batch'] ?? '');
if (empty($batch)) {
    echo json_encode(['success' => false, 'message' => 'Batch ID required']);
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, f.farm_name, f.farmer_name, f.avatar as farmer_avatar, f.location as farm_loc, f.cert_number 
                       FROM products p 
                       LEFT JOIN (
                           SELECT fm.id, fm.farm_name, u.name as farmer_name, fm.avatar, fm.farm_location_details as location, fm.cert_number 
                           FROM farmers fm 
                           JOIN users u ON fm.user_id = u.id
                       ) f ON p.farmer_id = f.id 
                       WHERE p.batch_id = ? LIMIT 1");
$stmt->execute([$batch]);
$prod = $stmt->fetch();

if ($prod) {
    echo json_encode([
        'success' => true,
        'batchId' => $prod['batch_id'],
        'name' => $prod['name'],
        'nameMs' => $prod['name_ms'],
        'price' => $prod['price_per_unit'],
        'unit' => $prod['unit'],
        'farmerName' => $prod['farmer_name'] ?? 'Pak Cik Azman',
        'farmName' => $prod['farm_name'] ?? 'Ladang Hijau Makmur',
        'location' => $prod['farm_location'] ?? 'Lunas, Kedah',
        'harvestDate' => $prod['harvest_date'],
        'grade' => $prod['grade'],
        'pesticide' => $prod['pesticide_status'],
        'storage' => $prod['storage_temp'],
        'cert' => $prod['cert_number'] ?? 'MYGAP-KDH-2024-0891',
        'image' => $prod['image_path']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Batch not found in MySQL database']);
}
?>