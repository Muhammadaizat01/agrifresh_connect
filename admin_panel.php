<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Famox Enterprise Admin Panel | AgriFresh Connect";
$activePage = 'admin';

$msg = '';
$err = '';

// Handle Delete Farmer POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete_farmer') {
    $delFarmerId = intval($_POST['farmer_id'] ?? 0);
    if ($delFarmerId > 0) {
        $fmStmt = $pdo->prepare("SELECT f.*, u.name as farmer_name FROM farmers f JOIN users u ON f.user_id = u.id WHERE f.id = ?");
        $fmStmt->execute([$delFarmerId]);
        $fmData = $fmStmt->fetch();

        if ($fmData) {
            $fName = $fmData['farmer_name'] ?: $fmData['farm_name'];
            // Delete associated products
            $pdo->prepare("DELETE FROM products WHERE farmer_id = ?")->execute([$delFarmerId]);
            // Delete farmer profile
            $pdo->prepare("DELETE FROM farmers WHERE id = ?")->execute([$delFarmerId]);

            // Log activity
            try {
                $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_name, action, description, ip_address) VALUES ('Famox Hub Admin', 'Farmer Removed', ?, ?)");
                $logStmt->execute(["Removed supplier partner '{$fName}' (ID: {$delFarmerId}) from Kedah network.", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            } catch (\Throwable $e) {}

            $msg = "Farmer <strong>" . htmlspecialchars($fName) . "</strong> has been successfully removed from registered suppliers.";
        }
    }
}

// Query Farmers
$farmers = $pdo->query("SELECT f.*, u.name as farmer_name, u.phone, u.email FROM farmers f JOIN users u ON f.user_id = u.id ORDER BY f.id ASC")->fetchAll();

$chartLabels = [];
$chartSalesData = [];
$chartVolumeData = [];

foreach ($farmers as &$fm) {
    // Sales aggregation
    $salesStmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(oi.subtotal), 0) as total_sales,
            COALESCE(SUM(oi.quantity), 0) as total_qty_sold,
            COUNT(DISTINCT oi.order_id) as total_orders
        FROM order_items oi
        JOIN products p ON (oi.product_name = p.name OR oi.product_name = p.name_ms)
        WHERE p.farmer_id = ?
    ");
    $salesStmt->execute([$fm['id']]);
    $sales = $salesStmt->fetch();

    $fm['total_sales'] = floatval($sales['total_sales'] ?? 0);
    $fm['total_qty_sold'] = floatval($sales['total_qty_sold'] ?? 0);
    $fm['total_orders'] = intval($sales['total_orders'] ?? 0);

    // Calculate last activity
    $actStmt = $pdo->prepare("SELECT MAX(created_at) as last_act FROM products WHERE farmer_id = ?");
    $actStmt->execute([$fm['id']]);
    $lastAct = $actStmt->fetchColumn();
    $fm['last_activity'] = $lastAct ?: ($fm['created_at'] ?? '2026-06-01 08:00:00');

    $days = (time() - strtotime($fm['last_activity'])) / 86400;
    $fm['days_inactive'] = (int)floor($days);

    $chartLabels[] = $fm['farmer_name'] ?: $fm['farm_name'];
    $chartSalesData[] = $fm['total_sales'];
    $chartVolumeData[] = $fm['total_qty_sold'];
}
unset($fm);

// Leaderboard sorted by sales desc
$farmersSortedBySales = $farmers;
usort($farmersSortedBySales, fn($a, $b) => $b['total_sales'] <=> $a['total_sales']);
$totalNetworkRevenue = array_sum(array_column($farmers, 'total_sales'));

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

    <?php if (!empty($msg)): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 16px; padding: 14px 20px; margin-bottom: 24px; color: #065f46; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <span>✓</span>
            <div><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <!-- Farmer Produce Sales Performance & Leaderboard Chart -->
    <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span style="font-size: 11px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">📊 Real-time Marketplace Analytics</span>
                <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin-top: 2px;">Farmer Sales Volume & Revenue Leaderboard</h2>
                <p style="font-size: 12px; color: #6b7280;">Compare which smallholder partner has achieved the highest volume and revenue from direct buyer sales.</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 10px;">
                    💰 Total Network Revenue: RM <?= number_format($totalNetworkRevenue, 2) ?>
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
            <!-- Chart Container -->
            <div style="background: #f9fafb; padding: 20px; border-radius: 20px; border: 1px solid #e5e7eb; min-height: 280px;">
                <div style="font-size: 12px; font-weight: 800; color: #374151; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span>📈 Produce Sales by Farmer (RM Revenue & Harvest Quantity)</span>
                    <span style="font-size: 10px; color: #6b7280;">Live Sync</span>
                </div>
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="farmerSalesChart"></canvas>
                </div>
            </div>

            <!-- Top Ranking Cards -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="font-size: 12px; font-weight: 800; color: #374151; margin-bottom: 4px;">🏆 Top Performing Producers</div>
                <?php foreach ($farmersSortedBySales as $idx => $fRank): 
                    $rankBadge = match($idx) {
                        0 => '🥇 #1 Top Seller',
                        1 => '🥈 #2 Runner-Up',
                        2 => '🥉 #3 Leading',
                        default => '#' . ($idx + 1) . ' Producer'
                    };
                    $badgeBg = match($idx) {
                        0 => '#fef3c7; color: #92400e; border: 1px solid #fde68a;',
                        1 => '#f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
                        2 => '#ffedd5; color: #9a3412; border: 1px solid #fed7aa;',
                        default => '#f9fafb; color: #64748b; border: 1px solid #e2e8f0;'
                    };
                ?>
                    <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 6px; background: <?= $badgeBg ?>">
                                <?= $rankBadge ?>
                            </span>
                            <div>
                                <div style="font-size: 12px; font-weight: 800; color: #111827;"><?= htmlspecialchars($fRank['farmer_name'] ?: $fRank['farm_name']) ?></div>
                                <div style="font-size: 10px; color: #6b7280;"><?= htmlspecialchars($fRank['farm_name']) ?> (<?= htmlspecialchars($fRank['kedah_district']) ?>)</div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13px; font-weight: 900; color: #059669;">RM <?= number_format($fRank['total_sales'], 2) ?></div>
                            <div style="font-size: 10px; color: #6b7280;"><?= floatval($fRank['total_qty_sold']) ?> kg sold</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Registered Supplier Farmers Table -->
    <div class="table-card" style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800; color: #111827;">Registered Supplier Farmers in Kedah</h2>
                <p style="font-size: 12px; color: #6b7280;">Active producer directory with activity tracker & 60-day inactivity management.</p>
            </div>
            <div style="font-size: 11px; background: #fff1f2; color: #991b1b; padding: 6px 12px; border-radius: 8px; border: 1px solid #fecaca; font-weight: 700;">
                ⚠️ Policy: Farmers inactive for more than 60 days can be decommissioned
            </div>
        </div>
        
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #374151;">Producer & Farm Name</th>
                    <th style="padding: 12px 8px; color: #374151;">District</th>
                    <th style="padding: 12px 8px; color: #374151;">Sales & Volume</th>
                    <th style="padding: 12px 8px; color: #374151;">Activity Status</th>
                    <th style="padding: 12px 8px; color: #374151;">Certification</th>
                    <th style="padding: 12px 8px; color: #374151; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($farmers as $fm): 
                    $isInactive = $fm['days_inactive'] > 60;
                ?>
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 14px 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?= htmlspecialchars($fm['avatar'] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300') ?>" alt="<?= htmlspecialchars($fm['farmer_name']) ?>" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover;">
                                <div>
                                    <div style="font-weight: 800; color: #111827;"><?= htmlspecialchars($fm['farmer_name']) ?></div>
                                    <div style="font-size: 11px; color: #059669; font-weight: 700;"><?= htmlspecialchars($fm['farm_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 8px; color: #4b5563;"><?= htmlspecialchars($fm['kedah_district']) ?></td>
                        <td style="padding: 14px 8px;">
                            <div style="font-weight: 800; color: #059669;">RM <?= number_format($fm['total_sales'], 2) ?></div>
                            <div style="font-size: 11px; color: #6b7280;"><?= floatval($fm['total_qty_sold']) ?> kg (<?= $fm['total_orders'] ?> orders)</div>
                        </td>
                        <td style="padding: 14px 8px;">
                            <?php if ($isInactive): ?>
                                <span style="background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; border: 1px solid #fca5a5; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>⚠️ Inactive (<?= $fm['days_inactive'] ?>d)</span>
                                </span>
                            <?php else: ?>
                                <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🟢 Active (<?= $fm['days_inactive'] ?>d ago)</span>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 14px 8px;">
                            <span style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                                <?= htmlspecialchars($fm['farming_certification'] ?: 'MyGAP') ?> (<?= htmlspecialchars($fm['cert_number']) ?>)
                            </span>
                        </td>
                        <td style="padding: 14px 8px; text-align: right;">
                            <form method="POST" action="admin_panel.php" onsubmit="return confirm('⚠️ Are you sure you want to remove <?= htmlspecialchars(addslashes($fm['farmer_name'] ?: $fm['farm_name'])) ?> from registered suppliers?<?= $isInactive ? ' Farmer has been inactive for ' . $fm['days_inactive'] . ' days.' : '' ?>');" style="display:inline;">
                                <input type="hidden" name="action" value="delete_farmer">
                                <input type="hidden" name="farmer_id" value="<?= $fm['id'] ?>">
                                <?php if ($isInactive): ?>
                                    <button type="submit" style="background: #dc2626; color: #ffffff; border: none; padding: 7px 12px; border-radius: 8px; font-weight: 800; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(220,38,38,0.3);">
                                        <span>🗑️ Remove Inactive</span>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>🗑️ Remove</span>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Hub Orders & Audit Logs Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Recent Orders -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Central Aggregation Orders</h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($orders as $ord): ?>
                    <div style="padding: 12px 16px; background: #f9fafb; border-radius: 14px; border: 1px solid #f3f4f6; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 800; margin-bottom: 4px;">
                            <span><?= htmlspecialchars($ord['order_number']) ?> • <?= htmlspecialchars($ord['buyer_name']) ?></span>
                            <span style="color: #059669;">RM <?= number_format($ord['total_amount'], 2) ?></span>
                        </div>
                        <div style="font-size: 11px; color: #6b7280;">
                            Batch: <code><?= htmlspecialchars($ord['batch_code']) ?></code> • <?= htmlspecialchars($ord['status']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Activity & Traceability Logs -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Traceability & System Logs</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($logs as $lg): ?>
                    <div style="padding: 10px 14px; background: #fdfdfd; border-radius: 12px; border: 1px solid #e5e7eb; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 700; color: #111827;">
                            <span><?= htmlspecialchars($lg['action']) ?></span>
                            <span style="font-size: 10px; color: #9ca3af;"><?= htmlspecialchars($lg['created_at']) ?></span>
                        </div>
                        <div style="font-size: 11px; color: #4b5563; margin-top: 2px;">
                            <?= htmlspecialchars($lg['description']) ?> (User: <?= htmlspecialchars($lg['user_name']) ?>)
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration for Farmer Produce Selling Visuals -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('farmerSalesChart');
    if (ctx) {
        const labels = <?= json_encode($chartLabels) ?>;
        const salesData = <?= json_encode($chartSalesData) ?>;
        const volumeData = <?= json_encode($chartVolumeData) ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Revenue (RM)',
                        data: salesData,
                        backgroundColor: 'rgba(5, 150, 105, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Produce Volume Sold (kg)',
                        data: volumeData,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: '#2563eb',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label.includes('Revenue')) {
                                    return label + ': RM ' + parseFloat(context.parsed.y).toFixed(2);
                                }
                                return label + ': ' + context.parsed.y + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Revenue (RM)', font: { size: 10, weight: 'bold' } },
                        ticks: {
                            callback: function(value) { return 'RM ' + value; }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Volume (kg)', font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
