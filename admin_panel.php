<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Famox Enterprise Admin Panel | AgriFresh Connect";
$activePage = 'admin';

// Query Farmers
$farmers = $pdo->query("SELECT f.*, u.name as farmer_name, u.phone, u.email FROM farmers f JOIN users u ON f.user_id = u.id ORDER BY f.id ASC")->fetchAll();

// Query Orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();

// Query Logs
$logs = $pdo->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 10")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container-custom" style="padding: 30px 1.25rem 60px;">
    <!-- Admin Header -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 28px; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #bfdbfe; color: #1e40af; padding: 2px 8px; border-radius: 4px;">
                Famox Enterprise Sdn Bhd (Lunas Central Hub)
            </span>
            <h1 style="font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px;">Central Supply Chain & Traceability Oversight</h1>
            <p style="font-size: 12px; color: #4b5563;">
                Direct oversight for smallholder partner farms in Lunas, Kulim, Baling, and Pokok Sena.
            </p>
        </div>

        <div style="display: flex; gap: 16px;">
            <div style="background: #ffffff; padding: 12px 18px; border-radius: 16px; border: 1px solid #bfdbfe; text-align: center;">
                <div style="font-size: 20px; font-weight: 900; color: #2563eb;"><?= count($farmers) ?></div>
                <div style="font-size: 11px; color: #6b7280;">Verified Producers</div>
            </div>
            <div style="background: #ffffff; padding: 12px 18px; border-radius: 16px; border: 1px solid #bfdbfe; text-align: center;">
                <div style="font-size: 20px; font-weight: 900; color: #059669;">100%</div>
                <div style="font-size: 11px; color: #6b7280;">MyGAP Compliance</div>
            </div>
        </div>
    </div>

    <!-- Registered Supplier Farmers Table -->
    <div class="table-card">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Registered Supplier Farmers in Kedah</h2>
        
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Producer & Farm Name</th>
                    <th>District</th>
                    <th>Certification</th>
                    <th>Tier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($farmers as $fm): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 800; color: #111827;"><?= htmlspecialchars($fm['farmer_name']) ?></div>
                            <div style="font-size: 11px; color: #6b7280;"><?= htmlspecialchars($fm['farm_name']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($fm['kedah_district']) ?></td>
                        <td>
                            <span style="background: #ecfdf5; color: #065f46; font-weight: 700; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                <?= htmlspecialchars($fm['farming_certification']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($fm['famox_tier']) ?></td>
                        <td>
                            <span style="color: #059669; font-weight: 700; font-size: 12px;">✓ Verified Active</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Stream & QR Validation Logs -->
    <div class="table-card">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Live Order & Cold-Chain Logistics Stream</h2>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($orders as $ord): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f9fafb; border-radius: 16px; border: 1px solid #f3f4f6; font-size: 12px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="font-weight: 800; color: #111827;">
                            <?= htmlspecialchars($ord['order_number']) ?> • <?= htmlspecialchars($ord['buyer_name']) ?> (<?= htmlspecialchars($ord['buyer_role']) ?>)
                        </div>
                        <div style="font-size: 11px; font-family: monospace; color: #6b7280;">
                            Batch: <?= htmlspecialchars($ord['batch_code']) ?> | Date: <?= htmlspecialchars($ord['created_at']) ?> | Payment: <?= htmlspecialchars($ord['payment_method']) ?>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 14px; font-weight: 900; color: #059669;">RM <?= number_format($ord['total_amount'], 2) ?></span>
                        <span style="background: #ecfdf5; color: #065f46; font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 9999px;">
                            <?= htmlspecialchars($ord['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
