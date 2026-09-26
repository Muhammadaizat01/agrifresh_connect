<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Farmer Portal | AgriFresh Connect";
$activePage = 'farmer';

$msg = '';
$err = '';
$newBatchId = '';

// Detect logged-in farmer or fallback to demo farmer 1
$farmerId = 1;
$farmerName = "Pak Cik Azman Bin Hashim";
$farmName = "Ladang Hijau Makmur Lunas";
$district = "Lunas, Kulim District, Kedah";
$certNumber = "MYGAP-KDH-2024-0891";
$avatar = "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300";
$farmerPhone = "+60 19-482 9102";
$farmerQuote = "With AgriFresh Connect and Famox, we get direct fair prices without middlemen.";

$targetUid = isLoggedIn() ? $_SESSION['user']['id'] : null;
if ($targetUid) {
    $stmt = $pdo->prepare("SELECT f.*, u.name as user_name, u.phone as user_phone FROM farmers f JOIN users u ON f.user_id = u.id WHERE f.user_id = ? LIMIT 1");
    $stmt->execute([$targetUid]);
} else {
    $stmt = $pdo->query("SELECT f.*, u.name as user_name, u.phone as user_phone FROM farmers f JOIN users u ON f.user_id = u.id WHERE f.id = 1 LIMIT 1");
}
$fData = $stmt->fetch();
if ($fData) {
    $farmerId = $fData['id'];
    $farmerName = $fData['user_name'];
    $farmerPhone = $fData['user_phone'] ?: '+60 19-482 9102';
    $farmName = $fData['farm_name'];
    $district = $fData['farm_location_details'] ?: $fData['kedah_district'];
    $certNumber = $fData['cert_number'] ?: 'MYGAP-KDH-2024-0891';
    $avatar = $fData['avatar'] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300';
    $farmerQuote = $fData['quote'] ?: 'With AgriFresh Connect and Famox, we get direct fair prices without middlemen.';
}

// Handle Add Crop POST with Photo Upload
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_produce') {
    $cropName = trim($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 2);
    $price = floatval($_POST['price'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'kg');
    $stock = floatval($_POST['stock'] ?? 100);
    $grade = trim($_POST['grade'] ?? 'Grade A Premium');
    $harvestDate = trim($_POST['harvest_date'] ?? date('Y-m-d 06:00:00'));
    $desc = trim($_POST['description'] ?? '');
    $location = trim($_POST['farm_location'] ?? $district);

    // Default image fallback
    $imagePath = 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800';

    // Handle File Upload
    if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['product_photo']['tmp_name'];
        $fileName = $_FILES['product_photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $uploadDir = __DIR__ . '/assets/img/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFileName = 'crop_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $imagePath = 'assets/img/uploads/' . $newFileName;
                $pubDir = __DIR__ . '/public/assets/img/uploads/';
                if (!is_dir($pubDir)) mkdir($pubDir, 0777, true);
                @copy($destPath, $pubDir . $newFileName);
            }
        }
    } elseif (!empty($_POST['image_url'])) {
        $imagePath = trim($_POST['image_url']);
    }

    if (!empty($cropName) && $price > 0) {
        $batchId = 'AF-LUN-' . date('Y') . '-' . rand(100, 999);
        $newBatchId = $batchId;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cropName)));
        $middlemanPrice = $price * 1.5;

        $ins = $pdo->prepare("INSERT INTO products (batch_id, farmer_id, category_id, name, name_ms, slug, description, description_ms, harvest_date, quantity_available, unit, price_per_unit, middleman_price, farm_location, grade, pesticide_status, image_path, tag, approval_status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'MyGAP Lab Tested (Safe 0.00 ppm)', ?, 'FRESH HARVEST', 'approved')");
        $ins->execute([$batchId, $farmerId, $categoryId, $cropName, $cropName, $slug, $desc, $desc, $harvestDate, $stock, $unit, $price, $middlemanPrice, $location, $grade, $imagePath]);
        
        $msg = "Success! <strong>" . htmlspecialchars($cropName) . "</strong> (Batch: <code>$batchId</code>) has been saved to MySQL and is now <strong>live in the Storefront</strong>.";
    } else {
        $err = "Please enter a valid produce name and direct price.";
    }
}

// Handle Profile Update POST with Avatar Upload
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $uName = trim($_POST['name'] ?? '');
    $uPhone = trim($_POST['phone'] ?? '');
    $fName = trim($_POST['farm_name'] ?? '');
    $fLoc = trim($_POST['location'] ?? '');
    $fQuote = trim($_POST['quote'] ?? '');
    $fCert = trim($_POST['cert_number'] ?? '');

    $newAvatar = $avatar;
    if (isset($_FILES['avatar_photo']) && $_FILES['avatar_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar_photo']['tmp_name'];
        $fileName = $_FILES['avatar_photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $uploadDir = __DIR__ . '/assets/img/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $newFileName = 'avatar_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $newAvatar = 'assets/img/uploads/' . $newFileName;
                $pubDir = __DIR__ . '/public/assets/img/uploads/';
                if (!is_dir($pubDir)) mkdir($pubDir, 0777, true);
                @copy($uploadDir . $newFileName, $pubDir . $newFileName);
            }
        }
    }

    if (isLoggedIn()) {
        $uId = $_SESSION['user']['id'];
        $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?")->execute([$uName, $uPhone, $uId]);
        $pdo->prepare("UPDATE farmers SET farm_name = ?, farm_location_details = ?, quote = ?, cert_number = ?, avatar = ? WHERE user_id = ?")->execute([$fName, $fLoc, $fQuote, $fCert, $newAvatar, $uId]);
        $_SESSION['user']['name'] = $uName;
        $_SESSION['user']['phone'] = $uPhone;
    } else {
        $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = 2")->execute([$uName, $uPhone]);
        $pdo->prepare("UPDATE farmers SET farm_name = ?, farm_location_details = ?, quote = ?, cert_number = ?, avatar = ? WHERE id = 1")->execute([$fName, $fLoc, $fQuote, $fCert, $newAvatar]);
    }

    $avatar = $newAvatar;
    $farmerName = $uName ?: $farmerName;
    $farmerPhone = $uPhone ?: $farmerPhone;
    $farmName = $fName ?: $farmName;
    $district = $fLoc ?: $district;
    $certNumber = $fCert ?: $certNumber;
    $farmerQuote = $fQuote ?: $farmerQuote;
    $msg = "Profile information and avatar photo updated successfully!";
}

// Handle Update Produce POST with Photo Replacement
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_produce') {
    $prodId = intval($_POST['product_id'] ?? 0);
    $cropName = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'kg');
    $stock = floatval($_POST['stock'] ?? 100);
    $grade = trim($_POST['grade'] ?? 'Grade A Premium');
    $desc = trim($_POST['description'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 2);

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$prodId]);
    $currProd = $stmt->fetch();

    if ($currProd && !empty($cropName) && $price > 0) {
        $imagePath = $currProd['image_path'];
        if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['product_photo']['tmp_name'];
            $fileName = $_FILES['product_photo']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($fileExt, $allowedExts)) {
                $uploadDir = __DIR__ . '/assets/img/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $newFileName = 'crop_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    $imagePath = 'assets/img/uploads/' . $newFileName;
                    $pubDir = __DIR__ . '/public/assets/img/uploads/';
                    if (!is_dir($pubDir)) mkdir($pubDir, 0777, true);
                    @copy($uploadDir . $newFileName, $pubDir . $newFileName);
                }
            }
        }

        $middlemanPrice = $price * 1.5;
        $upd = $pdo->prepare("UPDATE products SET name = ?, name_ms = ?, price_per_unit = ?, middleman_price = ?, unit = ?, quantity_available = ?, grade = ?, description = ?, description_ms = ?, category_id = ?, image_path = ? WHERE id = ?");
        $upd->execute([$cropName, $cropName, $price, $middlemanPrice, $unit, $stock, $grade, $desc, $desc, $categoryId, $imagePath, $prodId]);
        $msg = "Produce <strong>" . htmlspecialchars($cropName) . "</strong> and photo updated successfully!";
    }
}

// Handle Delete / Remove Produce POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_produce') {
    $delId = intval($_POST['product_id'] ?? 0);
    if ($delId > 0) {
        $delStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
        $delStmt->execute([$delId]);
        $prodToDel = $delStmt->fetch();
        if ($prodToDel) {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$delId]);
            $msg = "Produce <strong>" . htmlspecialchars($prodToDel['name']) . "</strong> has been permanently removed from your inventory.";
        }
    }
}

// Handle Quick Restock POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_restock') {
    $resId = intval($_POST['product_id'] ?? 0);
    $qty = floatval($_POST['stock_quantity'] ?? 50);
    if ($qty <= 0) $qty = 50;

    if ($resId > 0) {
        $pStmt = $pdo->prepare("SELECT name, unit FROM products WHERE id = ?");
        $pStmt->execute([$resId]);
        $prodInfo = $pStmt->fetch();
        if ($prodInfo) {
            $pdo->prepare("UPDATE products SET quantity_available = ? WHERE id = ?")->execute([$qty, $resId]);
            $msg = "Success! Produce <strong>" . htmlspecialchars($prodInfo['name']) . "</strong> has been restocked to <strong>{$qty} " . htmlspecialchars($prodInfo['unit']) . "</strong> and is now <strong>🟢 In Stock in the Storefront</strong>.";
        }
    }
}

// Handle Decline Order POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'decline_order') {
    $decOrderId = intval($_POST['order_id'] ?? 0);
    $decReason = trim($_POST['decline_reason'] ?? 'Produce is currently Out of Stock');
    $markSoldOut = isset($_POST['mark_sold_out']);

    if ($decOrderId > 0) {
        $updOrder = $pdo->prepare("UPDATE orders SET status = 'Declined (Out of Stock)', notes = ? WHERE id = ?");
        $updOrder->execute([$decReason, $decOrderId]);

        if ($markSoldOut) {
            $itemsStmt = $pdo->prepare("SELECT product_name FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$decOrderId]);
            $oItems = $itemsStmt->fetchAll();
            $soldOutStmt = $pdo->prepare("UPDATE products SET quantity_available = 0 WHERE name = ? OR name_ms = ?");
            foreach ($oItems as $oi) {
                $soldOutStmt->execute([$oi['product_name'], $oi['product_name']]);
            }
        }

        // Notify Buyer
        $ordUserStmt = $pdo->prepare("SELECT user_id, order_number, total_amount, buyer_name FROM orders WHERE id = ?");
        $ordUserStmt->execute([$decOrderId]);
        $ordInfo = $ordUserStmt->fetch();
        if ($ordInfo && !empty($ordInfo['user_id'])) {
            try {
                $notifStmt = $pdo->prepare("INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at) VALUES (?, 'App\\Notifications\\OrderDeclined', 'App\\Models\\User', ?, ?, NOW(), NOW())");
                $notifData = json_encode([
                    'order_id' => $decOrderId,
                    'order_number' => $ordInfo['order_number'],
                    'status' => 'Declined (Out of Stock)',
                    'reason' => $decReason,
                    'total_amount' => $ordInfo['total_amount'],
                    'message' => "We sincerely apologize! Your order {$ordInfo['order_number']} could not be fulfilled as produce is Out of Stock. Reason: {$decReason}. A full refund has been processed.",
                    'time' => date('d M Y, h:i A')
                ]);
                $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                $notifStmt->execute([$uuid, $ordInfo['user_id'], $notifData]);
            } catch (\Throwable $e) {}
        }

        $_SESSION['order_declined_alert'] = [
            'order_number' => $ordInfo['order_number'] ?? "#$decOrderId",
            'buyer_name' => $ordInfo['buyer_name'] ?? 'Buyer',
            'reason' => $decReason,
            'total_amount' => $ordInfo['total_amount'] ?? 0
        ];

        $msg = "Order <strong>" . htmlspecialchars($ordInfo['order_number'] ?? "#$decOrderId") . "</strong> declined. Produce marked Out of Stock and buyer has been notified with apology and refund confirmation.";
    }
}

// Fetch Farmer's Active Products
$prodStmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY id DESC");
$prodStmt->execute([$farmerId]);
$farmerProducts = $prodStmt->fetchAll();

// If user is new farmer and has no products yet, fallback to all farmer 1 products for demo
if (empty($farmerProducts)) {
    $farmerProducts = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 5")->fetchAll();
}

// Fetch Incoming Orders with items
$orderStmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 10");
$recentOrders = $orderStmt->fetchAll();
foreach ($recentOrders as &$ro) {
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $itemStmt->execute([$ro['id']]);
    $ro['items'] = $itemStmt->fetchAll();
}
unset($ro);

// Fetch Categories for dropdown
$cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container-custom" style="padding: 30px 1.25rem 60px;">
    <!-- Farmer Profile Header Card -->
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 28px; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="position: relative; cursor: pointer;" onclick="openProfileModal()" title="Click to change profile photo">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($farmerName) ?>" style="width: 70px; height: 70px; border-radius: 22px; object-fit: cover; border: 3px solid #d97706; box-shadow: 0 4px 10px rgba(217,119,6,0.2);">
                <div style="position: absolute; bottom: -4px; right: -4px; background: #111827; color: #ffffff; border: 2px solid #ffffff; border-radius: 50%; width: 26px; height: 26px; font-size: 11px; display: flex; align-items: center; justify-content: center;">
                    📷
                </div>
            </div>
            <div>
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fde68a; color: #78350f; padding: 2px 8px; border-radius: 4px;">
                    Famox Certified Supplier Portal (Lunas)
                </span>
                <h1 style="font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px;"><?= htmlspecialchars($farmName) ?></h1>
                <p style="font-size: 12px; color: #4b5563;">
                    Producer: <strong><?= htmlspecialchars($farmerName) ?></strong> • MyGAP Cert #<?= htmlspecialchars($certNumber) ?>
                </p>
                <p style="font-size: 11px; color: #059669; margin-top: 2px;">
                    📍 <?= htmlspecialchars($district) ?>
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="#orders" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 9px 15px; border-radius: 14px; text-decoration: none; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                <span>🔔</span>
                <span><?= count($recentOrders) ?> Orders to Fulfill</span>
            </a>
            <button type="button" onclick="openProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                <span>📷 Edit Profile & Photo</span>
            </button>
            <a href="index.php#store" class="btn-primary" style="padding: 10px 18px; font-size: 12px;">
                <span>View Storefront</span> &rarr;
            </a>
            <div style="background: #ffffff; padding: 8px 14px; border-radius: 14px; border: 1px solid #fde68a; text-align: center;">
                <div style="font-size: 16px; font-weight: 900; color: #059669;">+42%</div>
                <div style="font-size: 10px; color: #6b7280;">Income Gain</div>
            </div>
        </div>
    </div>

    <!-- Live Success Notice with Instant Link to Store -->
    <?php if ($msg): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 18px 24px; border-radius: 20px; border: 1px solid #a7f3d0; margin-bottom: 28px; font-size: 13px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-weight: 800; font-size: 15px; margin-bottom: 4px;">🎉 Produce Published to Store!</div>
                <div><?= $msg ?></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="index.php#store" class="btn-primary" style="padding: 8px 16px; font-size: 12px;">
                    🛒 See Live in Store &rarr;
                </a>
                <?php if ($newBatchId): ?>
                    <a href="qr_verify.php?batch=<?= urlencode($newBatchId) ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px; font-size: 12px;">
                        📱 View QR Passport
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Real-Time Buyer Order Notifications Banner -->
    <?php if (!empty($recentOrders)): ?>
        <?php $latestOrder = $recentOrders[0]; ?>
        <div style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); border: 1.5px solid #10b981; border-radius: 24px; padding: 20px 24px; margin-bottom: 28px; box-shadow: 0 6px 20px rgba(16,185,129,0.12); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; background: #10b981; color: #ffffff; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16,185,129,0.35);">
                    🔔
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #10b981; color: #ffffff; padding: 2px 8px; border-radius: 6px; letter-spacing: 0.5px;">BUYER ORDER NOTIFICATION</span>
                        <span style="font-size: 11px; color: #6b7280;"><?= !empty($latestOrder['created_at']) ? date('d M Y, h:i A', strtotime($latestOrder['created_at'])) : 'Just now' ?></span>
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: #111827; margin-top: 3px;">
                        <span>Buyer <strong><?= htmlspecialchars($latestOrder['buyer_name']) ?></strong> purchased vegetables</span>
                        <span style="color: #059669; margin-left: 6px;">(RM <?= number_format($latestOrder['total_amount'], 2) ?>)</span>
                    </div>
                    <div style="font-size: 12px; color: #374151; margin-top: 2px;">
                        🏷️ Trace Batch: <strong class="font-mono text-emerald"><?= htmlspecialchars($latestOrder['batch_code']) ?></strong> • 📞 Contact: <strong><?= htmlspecialchars($latestOrder['phone']) ?></strong> • 📍 <?= htmlspecialchars(explode(',', $latestOrder['shipping_address'])[0]) ?>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="#orders" class="btn-primary" style="padding: 10px 20px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>View All Buyer Orders & Items &darr;</span>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: 16px; border: 1px solid #fca5a5; margin-bottom: 24px; font-size: 13px;">
            <?= htmlspecialchars($err) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
        <!-- Left Form: Add Crop with Photo Upload -->
        <div style="background: #ffffff; padding: 32px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <span style="font-size: 22px;">📝</span>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800;">Register New Crop Harvest</h2>
                    <p style="font-size: 12px; color: #6b7280;">Upload your harvest photo, set fair price, and publish directly to MySQL storefront.</p>
                </div>
            </div>

            <form method="POST" action="farmer_dashboard.php" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr; gap: 16px; font-size: 12px;">
                <input type="hidden" name="action" value="add_produce">

                <!-- Photo Upload Box with Live Preview -->
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 6px;">Produce Photo (Upload from Device)</label>
                    <div style="border: 2px dashed #a7f3d0; background: #f0fdf4; border-radius: 18px; padding: 18px; text-align: center; cursor: pointer;" onclick="document.getElementById('photoInput').click()">
                        <input type="file" name="product_photo" id="photoInput" accept="image/*" style="display: none;" onchange="previewCropImage(this)">
                        
                        <div id="uploadPlaceholder">
                            <div style="font-size: 32px; margin-bottom: 6px;">📸</div>
                            <div style="font-weight: 800; color: #065f46; font-size: 13px;">Click to Select Harvest Photo</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Supports JPG, PNG, WEBP from your computer or phone</div>
                        </div>

                        <div id="imagePreviewContainer" style="display: none; align-items: center; justify-content: center; gap: 14px;">
                            <img id="previewImg" src="" alt="Preview" style="max-height: 120px; border-radius: 12px; object-fit: cover; border: 2px solid #10b981; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <div style="text-align: left;">
                                <div id="previewFileName" style="font-weight: 800; color: #111827; font-size: 12px;"></div>
                                <div style="font-size: 11px; color: #059669; font-weight: 700;">✓ Ready to upload</div>
                                <button type="button" onclick="event.stopPropagation(); clearImageUpload();" class="btn-dark" style="padding: 4px 10px; font-size: 10px; margin-top: 6px;">Change Photo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Crop / Produce Name</label>
                    <input type="text" name="name" placeholder="e.g. Lunas Sweet Cherry Tomatoes" required class="form-input" style="width: 100%; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Category</label>
                        <select name="category_id" class="form-input" style="width: 100%;">
                            <?php foreach ($cats as $ct): ?>
                                <option value="<?= $ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Quality Standard</label>
                        <select name="grade" class="form-input" style="width: 100%;">
                            <option value="Grade A Premium">Grade A Premium</option>
                            <option value="Grade B Standard">Grade B Standard</option>
                            <option value="MyGAP Organic Export">MyGAP Organic Export</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Direct Price (RM)</label>
                        <input type="number" step="any" min="0.10" name="price" placeholder="4.20" required class="form-input" style="width: 100%; font-weight: 800; color: #059669;">
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Unit</label>
                        <select name="unit" class="form-input" style="width: 100%;">
                            <option value="kg">per kg</option>
                            <option value="250g pack">per 250g pack</option>
                            <option value="ear (tongkol)">per ear (tongkol)</option>
                            <option value="box (6kg)">per curated box</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Harvest Quantity (Available Stock)</label>
                        <input type="number" step="any" min="0" name="stock" placeholder="150" value="150" class="form-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Harvest Timestamp</label>
                        <input type="text" name="harvest_date" value="<?= date('Y-m-d') ?> 05:30 AM" class="form-input" style="width: 100%;">
                    </div>
                </div>

                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Description / Provenance Notes</label>
                    <textarea name="description" rows="2" placeholder="Plucked at dawn in Lunas, rich in sweetness, 100% pesticide tested." class="form-input" style="width: 100%;"></textarea>
                </div>

                <div style="text-align: center; margin-top: 8px;">
                    <button type="submit" class="btn-primary" style="padding: 14px 36px; font-size: 14px; width: 100%; max-width: 340px; margin: 0 auto; display: inline-flex; justify-content: center; box-shadow: 0 4px 14px rgba(5,150,105,0.3);">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Generate QR & Publish to Store</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Produce Inventory & QR Codes (With Edit Button) -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 18px; font-weight: 800;">Active Produce & Printable QR Codes</h2>
                    <p style="font-size: 12px; color: #6b7280;">You can self-update photo, price, and details for any published produce anytime.</p>
                </div>
                <a href="index.php#store" style="font-size: 12px; color: #059669; font-weight: 700;">View in Storefront &rarr;</a>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($farmerProducts as $fp): ?>
                    <?php $isSoldOut = (float)$fp['quantity_available'] <= 0; ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: <?= $isSoldOut ? '#fff1f2' : '#f9fafb' ?>; border-radius: 18px; border: 1px solid <?= $isSoldOut ? '#fecdd3' : '#f3f4f6' ?>; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="position: relative;">
                                <img src="<?= htmlspecialchars($fp['image_path']) ?>" alt="<?= htmlspecialchars($fp['name']) ?>" style="width: 58px; height: 58px; border-radius: 14px; object-fit: cover; border: 1px solid <?= $isSoldOut ? '#fca5a5' : '#e5e7eb' ?>; <?= $isSoldOut ? 'filter: grayscale(40%);' : '' ?>">
                                <?php if ($isSoldOut): ?>
                                    <span style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); background: #dc2626; color: #fff; font-size: 8px; font-weight: 800; padding: 1px 5px; border-radius: 4px; white-space: nowrap;">SOLD OUT</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="font-size: 14px; font-weight: 800; color: #111827;"><?= htmlspecialchars($fp['name']) ?></div>
                                    <?php if ($isSoldOut): ?>
                                        <button type="button" onclick="openRestockModal('<?= $fp['id'] ?>', '<?= htmlspecialchars(addslashes($fp['name'])) ?>', '<?= htmlspecialchars($fp['unit']) ?>')" style="background: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; border: 1.5px solid #fca5a5; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Click to quickly restock this produce">
                                            <span>🔴 SOLD OUT</span>
                                            <span style="background: #dc2626; color: #fff; font-size: 8px; padding: 1px 4px; border-radius: 4px;">Restock ↻</span>
                                        </button>
                                    <?php else: ?>
                                        <span style="background: #dcfce7; color: #16a34a; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; border: 1px solid #bbf7d0;">🟢 In Stock</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 11px; font-family: monospace; color: #059669; font-weight: 700; margin-top: 2px;">
                                    Batch: <?= htmlspecialchars($fp['batch_id']) ?> • RM <?= number_format($fp['price_per_unit'], 2) ?>/<?= htmlspecialchars($fp['unit']) ?>
                                </div>
                                <div style="font-size: 10px; color: <?= $isSoldOut ? '#b91c1c' : '#6b7280' ?>; margin-top: 1px; font-weight: <?= $isSoldOut ? '700' : 'normal' ?>;">
                                    Stock: <?= htmlspecialchars($fp['quantity_available']) ?> <?= htmlspecialchars($fp['unit']) ?> • <?= htmlspecialchars($fp['grade']) ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <?php if ($isSoldOut): ?>
                                <button type="button" onclick="openRestockModal('<?= $fp['id'] ?>', '<?= htmlspecialchars(addslashes($fp['name'])) ?>', '<?= htmlspecialchars($fp['unit']) ?>')" style="background: #ecfdf5; color: #065f46; border: 1.5px solid #10b981; padding: 6px 14px; font-size: 11px; border-radius: 8px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(16,185,129,0.15);" title="Restock this produce to mark it back In Stock">
                                    <span>🟢 Restock Crop</span>
                                </button>
                            <?php endif; ?>
                            <button type="button" onclick='openEditCropModal(<?= json_encode($fp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="btn-emerald" style="padding: 6px 14px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                ✏️ <span>Edit Crop & Photo</span>
                            </button>
                            <a href="qr_verify.php?batch=<?= htmlspecialchars($fp['batch_id']) ?>" target="_blank" class="btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                                View Passport &rarr;
                            </a>
                            <button type="button" onclick="openQRModalByBatch('<?= htmlspecialchars($fp['batch_id'], ENT_QUOTES) ?>')" class="btn-dark" style="padding: 6px 12px; font-size: 11px;">
                                Print QR
                            </button>
                            <button type="button" onclick="confirmDeleteProduce(<?= $fp['id'] ?>, '<?= htmlspecialchars(addslashes($fp['name'])) ?>')" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 12px; font-size: 11px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;" title="Remove this produce from catalog">
                                🗑️ <span>Remove</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Hidden Form for Produce Deletion -->
            <form id="deleteProduceForm" method="POST" action="farmer_dashboard.php" style="display: none;">
                <input type="hidden" name="action" value="delete_produce">
                <input type="hidden" name="product_id" id="deleteProduceId" value="">
            </form>
        </div>

        <!-- Incoming Orders from Buyers with Traceability & Items -->
        <div id="orders" style="background: #ffffff; padding: 32px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 22px;">📦</span>
                        <h2 style="font-size: 18px; font-weight: 800; color: #111827;">Incoming Orders from Buyers & Traceability</h2>
                    </div>
                    <p style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                        Check who bought your vegetables, contact buyers, and trace each crop batch with verified QR passports.
                    </p>
                </div>
                <div style="background: #ecfdf5; color: #065f46; font-size: 12px; font-weight: 800; padding: 6px 14px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                    🔔 <?= count($recentOrders) ?> Orders Recorded
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <?php if (empty($recentOrders)): ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <div style="font-size: 36px; margin-bottom: 8px;">🌱</div>
                        <p style="font-weight: 700; color: #111827;">No orders received yet.</p>
                        <p style="font-size: 12px; margin-top: 4px;">Orders placed by buyers will instantly appear here with full buyer details and traceability.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ro): ?>
                        <?php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $ro['phone'] ?? '');
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '60' . substr($cleanPhone, 1);
                            }
                        ?>
                        <div style="padding: 22px; background: #ffffff; border-radius: 22px; border: 1.5px solid #d1fae5; box-shadow: 0 4px 12px rgba(16,185,129,0.06); font-size: 12px;">
                            <!-- Order Header Bar -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <span style="font-family: monospace; font-weight: 800; font-size: 14px; background: #111827; color: #ffffff; padding: 4px 10px; border-radius: 8px;">
                                        <?= htmlspecialchars($ro['order_number']) ?>
                                    </span>
                                    <?php if (str_contains(strtolower($ro['status'] ?? ''), 'decline')): ?>
                                        <span style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px; border: 1px solid #fca5a5;">
                                            ❌ <?= htmlspecialchars($ro['status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                                            ✓ <?= htmlspecialchars($ro['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span style="font-size: 11px; color: #6b7280;">
                                        🗓️ <?= !empty($ro['created_at']) ? date('d M Y, h:i A', strtotime($ro['created_at'])) : 'Today' ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <div style="color: #059669; font-size: 18px; font-weight: 900;">
                                        RM <?= number_format($ro['total_amount'], 2) ?>
                                    </div>
                                    <div style="font-size: 10px; color: #6b7280;">
                                        Paid via <?= htmlspecialchars($ro['payment_method']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Buyer Information & Contact -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin: 16px 0; padding: 14px; background: #f9fafb; border-radius: 16px; border: 1px solid #f3f4f6;">
                                <div>
                                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #6b7280; margin-bottom: 4px;">
                                        👤 Buyer Profile & Contact
                                    </div>
                                    <div style="font-size: 14px; font-weight: 800; color: #111827;">
                                        <?= htmlspecialchars($ro['buyer_name']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 1px;">
                                        🏷️ <?= htmlspecialchars($ro['buyer_role'] ?? 'Direct Consumer') ?>
                                    </div>
                                    <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <span style="font-size: 12px; color: #374151; font-weight: 600;">📞 <?= htmlspecialchars($ro['phone'] ?? 'Not provided') ?></span>
                                        <?php if (!empty($ro['phone'])): ?>
                                            <a href="tel:<?= htmlspecialchars($ro['phone']) ?>" style="background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 11px;">
                                                Call
                                            </a>
                                            <?php if (!empty($cleanPhone)): ?>
                                                <a href="https://wa.me/<?= htmlspecialchars($cleanPhone) ?>?text=Hi%20<?= urlencode($ro['buyer_name']) ?>,%20this%20is%20your%20AgriFresh%20Kedah%20farmer%20regarding%20order%20<?= urlencode($ro['order_number']) ?>." target="_blank" style="background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 3px;">
                                                    <span>WhatsApp</span>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div>
                                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #6b7280; margin-bottom: 4px;">
                                        🚚 Delivery Destination & Logistics
                                    </div>
                                    <div style="font-size: 12px; color: #111827; line-height: 1.4;">
                                        📍 <strong><?= htmlspecialchars($ro['shipping_address']) ?></strong>
                                    </div>
                                    <div style="font-size: 11px; color: #4b5563; margin-top: 4px;">
                                        Assigned Van: <strong><?= htmlspecialchars($ro['driver']) ?></strong>
                                    </div>
                                    <?php if (!empty($ro['notes'])): ?>
                                        <div style="font-size: 11px; color: #d97706; font-style: italic; margin-top: 3px;">
                                            Note: "<?= htmlspecialchars($ro['notes']) ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Vegetables Purchased Table -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #374151; margin-bottom: 8px;">
                                    🥬 Vegetables & Produce in this Order:
                                </div>
                                <?php if (!empty($ro['items'])): ?>
                                    <div style="overflow-x: auto;">
                                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                            <thead>
                                                <tr style="background: #f0fdf4; border-bottom: 1.5px solid #bbf7d0; text-align: left;">
                                                    <th style="padding: 8px 12px; font-weight: 800; color: #065f46;">Produce Name</th>
                                                    <th style="padding: 8px 12px; font-weight: 800; color: #065f46; text-align: center;">Quantity Ordered</th>
                                                    <th style="padding: 8px 12px; font-weight: 800; color: #065f46; text-align: right;">Unit Price</th>
                                                    <th style="padding: 8px 12px; font-weight: 800; color: #065f46; text-align: right;">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ro['items'] as $item): ?>
                                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                                        <td style="padding: 10px 12px; font-weight: 700; color: #111827;">
                                                            🌱 <?= htmlspecialchars($item['product_name']) ?>
                                                        </td>
                                                        <td style="padding: 10px 12px; text-align: center; font-weight: 800; color: #059669;">
                                                            <?= number_format($item['quantity'], 0) ?> <?= htmlspecialchars($item['unit']) ?>
                                                        </td>
                                                        <td style="padding: 10px 12px; text-align: right; color: #4b5563;">
                                                            RM <?= number_format($item['price'], 2) ?>
                                                        </td>
                                                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #111827;">
                                                            RM <?= number_format($item['subtotal'], 2) ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div style="background: #f9fafb; padding: 12px; border-radius: 12px; color: #6b7280; font-size: 12px;">
                                        Direct harvest batch items tagged to <strong><?= htmlspecialchars($ro['batch_code']) ?></strong>.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Traceability & QR Actions Bar -->
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #f3f4f6; flex-wrap: wrap; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 11px; color: #6b7280; font-weight: 700;">QR Trace Batch:</span>
                                    <span class="font-mono text-emerald" style="font-weight: 900; background: #ecfdf5; padding: 3px 8px; border-radius: 6px; border: 1px solid #a7f3d0; font-size: 11px;">
                                        <?= htmlspecialchars($ro['batch_code']) ?>
                                    </span>
                                </div>

                                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                                    <a href="qr_verify.php?batch=<?= urlencode($ro['batch_code']) ?>" target="_blank" class="btn-secondary" style="padding: 8px 16px; font-size: 11px; display: inline-flex; align-items: center; gap: 5px;">
                                        <span>📱 Trace QR Passport</span> &rarr;
                                    </a>
                                    <button type="button" onclick="openQRModalByBatch('<?= htmlspecialchars($ro['batch_code'], ENT_QUOTES) ?>')" class="btn-dark" style="padding: 8px 16px; font-size: 11px; display: inline-flex; align-items: center; gap: 5px;">
                                        <span>🖨️ Print Crate QR Sticker</span>
                                    </button>
                                    <?php if (!str_contains(strtolower($ro['status'] ?? ''), 'decline')): ?>
                                        <button type="button" onclick="openDeclineOrderModal('<?= $ro['id'] ?>', '<?= htmlspecialchars(addslashes($ro['order_number'])) ?>', '<?= htmlspecialchars(addslashes($ro['buyer_name'])) ?>')" style="background: #fff1f2; color: #dc2626; border: 1px solid #fecaca; padding: 8px 14px; font-size: 11px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Decline if produce is out of stock and send apology">
                                            <span>❌ Decline (Out of Stock)</span>
                                        </button>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                                            Declined: <?= htmlspecialchars($ro['notes'] ?? 'Out of Stock') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Edit Profile & Photo Modal -->
<div id="profileModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📷</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Update Profile & Photo</h3>
                    <p style="font-size: 11px; color: #6b7280;">Change your farmer avatar, name, and farm credentials.</p>
                </div>
            </div>
            <button type="button" onclick="closeProfileModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="profileForm" action="farmer_dashboard.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            <input type="hidden" name="action" value="update_profile">

            <!-- Avatar Uploader Preview -->
            <div style="text-align: center; padding: 16px; background: #f9fafb; border-radius: 20px; border: 1px solid #e5e7eb;">
                <img id="avatarModalPreview" src="<?= htmlspecialchars($avatar) ?>" alt="Avatar Preview" style="width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; margin: 0 auto 10px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="btn-primary" style="padding: 6px 14px; font-size: 11px; margin: 0 auto; cursor: pointer;">
                    📁 Choose Photo from Device
                </button>
                <input type="file" name="avatar_photo" id="avatarFileInput" accept="image/*" style="display: none;" onchange="previewAvatarImage(this)">
                <div id="avatarFileNameDisplay" style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 6px;"></div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Producer Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($farmerName) ?>" required class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Name / Ladang</label>
                <input type="text" name="farm_name" value="<?= htmlspecialchars($farmName) ?>" required class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($farmerPhone) ?>" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">MyGAP Cert Number</label>
                    <input type="text" name="cert_number" value="<?= htmlspecialchars($certNumber) ?>" class="form-input" style="width: 100%;">
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Location Details</label>
                <input type="text" name="location" value="<?= htmlspecialchars($district) ?>" class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farmer Quote / Motto</label>
                <textarea name="quote" rows="2" class="form-input" style="width: 100%;"><?= htmlspecialchars($farmerQuote) ?></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" id="profileSubmitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">💾 Save Profile & Photo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Produce Crop & Photo Modal -->
<div id="editCropModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 540px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">✏️</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Edit Produce & Photo</h3>
                    <p style="font-size: 11px; color: #6b7280;" id="editCropBatchTag">Batch: ...</p>
                </div>
            </div>
            <button type="button" onclick="closeEditCropModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="editCropForm" action="farmer_dashboard.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            <input type="hidden" name="action" value="update_produce">
            <input type="hidden" name="product_id" id="editCropProductId" value="">

            <!-- Product Image Preview & Uploader -->
            <div style="text-align: center; padding: 16px; background: #f9fafb; border-radius: 20px; border: 1px solid #e5e7eb;">
                <img id="editCropPhotoPreview" src="" alt="Crop Photo Preview" style="max-height: 120px; border-radius: 14px; object-fit: cover; border: 2px solid #10b981; margin: 0 auto 10px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="document.getElementById('editCropFileInput').click()" class="btn-primary" style="padding: 6px 14px; font-size: 11px; margin: 0 auto; cursor: pointer;">
                    📸 Replace Crop Photo from Device
                </button>
                <input type="file" name="product_photo" id="editCropFileInput" accept="image/*" style="display: none;" onchange="previewEditCropImage(this)">
                <div id="editCropFileNameDisplay" style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 6px;"></div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Crop / Produce Name</label>
                <input type="text" name="name" id="editCropName" required class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Direct Price (RM)</label>
                    <input type="number" step="any" min="0.10" name="price" id="editCropPrice" required class="form-input" style="width: 100%; font-weight: 800; color: #059669;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Unit</label>
                    <select name="unit" id="editCropUnit" class="form-input" style="width: 100%;">
                        <option value="kg">per kg</option>
                        <option value="250g pack">per 250g pack</option>
                        <option value="ear (tongkol)">per ear (tongkol)</option>
                        <option value="box (6kg)">per curated box</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-weight: 700;">Available Stock Quantity</label>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 0;" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 2px 8px; font-size: 10px; font-weight: 800; cursor: pointer;" title="Set stock to 0 to mark this vegetable as sold out">
                            🔴 Mark Sold Out (0)
                        </button>
                    </div>
                    <input type="number" step="any" min="0" name="stock" id="editCropStock" class="form-input" style="width: 100%;">
                    <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 10px; color: #6b7280; font-weight: 700;">Quick Set:</span>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 25;" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; cursor: pointer;">25</button>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 50;" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; cursor: pointer;">50</button>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 100;" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; cursor: pointer;">100</button>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 200;" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; cursor: pointer;">200</button>
                    </div>
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Quality Standard</label>
                    <select name="grade" id="editCropGrade" class="form-input" style="width: 100%;">
                        <option value="Grade A Premium">Grade A Premium</option>
                        <option value="Grade B Standard">Grade B Standard</option>
                        <option value="MyGAP Organic Export">MyGAP Organic Export</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Category</label>
                <select name="category_id" id="editCropCategory" class="form-input" style="width: 100%;">
                    <?php foreach ($cats as $ct): ?>
                        <option value="<?= $ct['id'] ?>"><?= htmlspecialchars($ct['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Description / Provenance Notes</label>
                <textarea name="description" id="editCropDescription" rows="2" class="form-input" style="width: 100%;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeEditCropModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" id="editCropSubmitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">💾 Update Produce & Photo</button>
            </div>
        </form>
    </div>
</div>

<script>
// Crop Photo Preview for New Crop Form
function previewCropImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('uploadPlaceholder').style.display = 'none';
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewFileName').innerText = file.name;
            document.getElementById('imagePreviewContainer').style.display = 'flex';
        };
        
        reader.readAsDataURL(file);
    }
}

function clearImageUpload() {
    const input = document.getElementById('photoInput');
    input.value = '';
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('imagePreviewContainer').style.display = 'none';
}

// Profile Modal Handlers
function openProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'flex';
}

function closeProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarModalPreview').src = e.target.result;
            document.getElementById('avatarFileNameDisplay').innerText = '✓ Selected: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

// Edit Crop Modal Handlers
function openEditCropModal(product) {
    const modal = document.getElementById('editCropModalBackdrop');
    if (!modal) return;

    document.getElementById('editCropProductId').value = product.id;
    document.getElementById('editCropBatchTag').innerText = 'Batch ID: ' + (product.batch_id || 'N/A');
    document.getElementById('editCropName').value = product.name || '';
    document.getElementById('editCropPrice').value = parseFloat(product.price_per_unit || 0).toFixed(2);
    document.getElementById('editCropUnit').value = product.unit || 'kg';
    document.getElementById('editCropStock').value = parseFloat(product.quantity_available || 100);
    document.getElementById('editCropGrade').value = product.grade || 'Grade A Premium';
    document.getElementById('editCropCategory').value = product.category_id || 2;
    document.getElementById('editCropDescription').value = product.description || '';

    const imgPreview = document.getElementById('editCropPhotoPreview');
    const imgPath = product.image_path || '';
    imgPreview.src = imgPath;
    document.getElementById('editCropFileNameDisplay').innerText = '';

    modal.style.display = 'flex';
}

function closeEditCropModal() {
    const modal = document.getElementById('editCropModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function previewEditCropImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editCropPhotoPreview').src = e.target.result;
            document.getElementById('editCropFileNameDisplay').innerText = '✓ Ready to replace with: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

function confirmDeleteProduce(id, name) {
    const msg = "Are you sure you want to remove '" + name + "' from your produce catalog?\n\nTip: If it is only temporarily out of stock, you can click 'Edit Crop' and set stock to 0 or click 'Mark Sold Out' instead of deleting it.";
    if (confirm(msg)) {
        document.getElementById('deleteProduceId').value = id;
        document.getElementById('deleteProduceForm').submit();
    }
}

// Decline Modal Handlers
function openDeclineOrderModal(orderId, orderNum, buyerName) {
    const modal = document.getElementById('declineOrderModalBackdrop');
    const orderIdInput = document.getElementById('declineOrderId');
    const tag = document.getElementById('declineModalOrderTag');
    if (modal && orderIdInput) {
        orderIdInput.value = orderId;
        if (tag) tag.innerText = 'Order: ' + orderNum + ' (' + buyerName + ')';
        modal.style.display = 'flex';
    }
}

function closeDeclineOrderModal() {
    const modal = document.getElementById('declineOrderModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function handleDeclineReasonChange(sel) {
    const customArea = document.getElementById('customDeclineReason');
    if (customArea) {
        if (sel.value === 'custom') {
            customArea.style.display = 'block';
            customArea.required = true;
            customArea.name = 'decline_reason';
            sel.name = 'predefined_reason';
        } else {
            customArea.style.display = 'none';
            customArea.required = false;
            customArea.name = 'custom_decline_reason';
            sel.name = 'decline_reason';
        }
    }
}

// Restock Modal Handlers
function openRestockModal(prodId, prodName, unit) {
    const modal = document.getElementById('restockModalBackdrop');
    const prodInput = document.getElementById('restockProductId');
    const tag = document.getElementById('restockModalCropTag');
    const unitTag = document.getElementById('restockUnitTag');
    if (modal && prodInput) {
        prodInput.value = prodId;
        if (tag) tag.innerText = 'Crop: ' + prodName;
        if (unitTag) unitTag.innerText = unit || 'kg';
        modal.style.display = 'flex';
    }
}

function closeRestockModal() {
    const modal = document.getElementById('restockModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function setRestockQty(qty) {
    const inp = document.getElementById('restockQuantityInput');
    if (inp) inp.value = qty;
}

// Close modals when clicking on backdrop
window.addEventListener('click', function(e) {
    const profileModal = document.getElementById('profileModalBackdrop');
    const cropModal = document.getElementById('editCropModalBackdrop');
    const declineModal = document.getElementById('declineOrderModalBackdrop');
    const restockModal = document.getElementById('restockModalBackdrop');
    if (e.target === profileModal) profileModal.style.display = 'none';
    if (e.target === cropModal) cropModal.style.display = 'none';
    if (e.target === declineModal) declineModal.style.display = 'none';
    if (e.target === restockModal) restockModal.style.display = 'none';
});

// Submit Feedback Handlers
document.addEventListener('DOMContentLoaded', function() {
    const pForm = document.getElementById('profileForm');
    if (pForm) {
        pForm.addEventListener('submit', function() {
            const btn = document.getElementById('profileSubmitBtn');
            if (btn) {
                btn.innerHTML = '⏳ Saving Profile...';
                btn.style.opacity = '0.75';
            }
        });
    }

    const cForm = document.getElementById('editCropForm');
    if (cForm) {
        cForm.addEventListener('submit', function() {
            const btn = document.getElementById('editCropSubmitBtn');
            if (btn) {
                btn.innerHTML = '⏳ Saving Crop...';
                btn.style.opacity = '0.75';
            }
        });
    }
});
</script>

<!-- Modal 3: Decline Order & Out of Stock Apology Modal -->
<div id="declineOrderModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">❌</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Decline Order & Out of Stock Notice</h3>
                    <p style="font-size: 11px; color: #6b7280;" id="declineModalOrderTag">Order: ...</p>
                </div>
            </div>
            <button type="button" onclick="closeDeclineOrderModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="declineOrderForm" action="farmer_dashboard.php" style="display: flex; flex-direction: column; gap: 16px; font-size: 12px;">
            <input type="hidden" name="action" value="decline_order">
            <input type="hidden" name="order_id" id="declineOrderId" value="">

            <div style="background: #fff1f2; border: 1px solid #fecaca; border-radius: 16px; padding: 14px; color: #991b1b; line-height: 1.4;">
                <strong>⚠️ Note to Buyer:</strong> Declining this order will instantly notify the buyer with an apology message and issue an automated refund guarantee.
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 6px;">Reason for Declining (Sent to Buyer)</label>
                <select name="decline_reason" id="declineReasonSelect" onchange="handleDeclineReasonChange(this)" class="form-input" style="width: 100%; margin-bottom: 8px;">
                    <option value="Harvest Out of Stock - Dawn crop depleted due to high demand">Harvest Out of Stock - Dawn crop depleted due to high demand</option>
                    <option value="Quality Standard Notice - Harvest did not meet MyGAP Grade-A standard">Quality Standard Notice - Harvest did not meet MyGAP Grade-A standard</option>
                    <option value="Weather Disruption - Heavy rain prevented morning plucking in Kedah">Weather Disruption - Heavy rain prevented morning plucking in Kedah</option>
                    <option value="custom">Other / Custom Reason...</option>
                </select>
                <textarea name="custom_decline_reason" id="customDeclineReason" rows="2" placeholder="Write custom apology / explanation for the buyer..." class="form-input" style="width: 100%; display: none;"></textarea>
            </div>

            <div style="background: #f9fafb; padding: 12px; border-radius: 12px; border: 1px solid #f3f4f6;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: #111827;">
                    <input type="checkbox" name="mark_sold_out" value="1" checked style="width: 16px; height: 16px; accent-color: #dc2626;">
                    <span>Automatically mark this produce as 🔴 SOLD OUT (0 stock) in store catalog</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeDeclineOrderModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #dc2626; color: #ffffff; border: none; border-radius: 12px; padding: 10px 22px; font-size: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(220,38,38,0.3);">
                    <span>Confirm Decline & Send Apology</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Quick Restock Produce Modal -->
<div id="restockModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 480px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🟢</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Restock Fresh Produce</h3>
                    <p style="font-size: 11px; color: #6b7280;" id="restockModalCropTag">Produce: ...</p>
                </div>
            </div>
            <button type="button" onclick="closeRestockModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="restockForm" action="farmer_dashboard.php" style="display: flex; flex-direction: column; gap: 16px; font-size: 12px;">
            <input type="hidden" name="action" value="quick_restock">
            <input type="hidden" name="product_id" id="restockProductId" value="">

            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 16px; padding: 14px; color: #065f46; line-height: 1.4;">
                <strong>🌱 Putting Produce Back In Stock:</strong> Entering available stock will immediately change status from 🔴 <strong>SOLD OUT</strong> to 🟢 <strong>In Stock</strong> live on the Storefront!
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 6px;">Quick Quantity Presets</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                    <button type="button" onclick="setRestockQty(25)" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-weight: 800; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 11px;">+25</button>
                    <button type="button" onclick="setRestockQty(50)" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; font-weight: 800; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 11px;">+50</button>
                    <button type="button" onclick="setRestockQty(100)" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-weight: 800; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 11px;">+100</button>
                    <button type="button" onclick="setRestockQty(250)" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-weight: 800; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-size: 11px;">+250</button>
                </div>

                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Available Harvest Stock Quantity (<span id="restockUnitTag">kg</span>)</label>
                <input type="number" step="any" min="1" name="stock_quantity" id="restockQuantityInput" value="50" required class="form-input" style="width: 100%; font-size: 16px; font-weight: 800; color: #059669;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeRestockModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-size: 12px; font-weight: 800; cursor: pointer;">
                    <span>💾 Restock & Publish In Stock</span> &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
