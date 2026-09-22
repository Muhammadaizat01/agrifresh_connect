<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Checkout | AgriFresh Connect";
$activePage = 'checkout';

$user = currentUser();
$orderSuccess = null;

// If user is logged in and submits the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=checkout.php");
        exit;
    }

    $name = trim($_POST['name'] ?? $user['name']);
    $phone = trim($_POST['phone'] ?? $user['phone']);
    $address = trim($_POST['address'] ?? ($user['address'] ?: 'No. 12, Jalan Lunas Makmur 3, 09600 Lunas, Kedah'));
    $paymentMethod = trim($_POST['payment_method'] ?? 'FPX Online Banking (Maybank)');
    $totalAmount = floatval($_POST['total_amount'] ?? 45.40);
    $cartJson = $_POST['cart_data'] ?? '[]';
    $items = json_decode($cartJson, true) ?: [];

    $orderNum = 'ORD-KDH-' . rand(1000, 9999);
    $batchCode = !empty($items) ? $items[0]['batchId'] : 'AF-LUN-2026-089';

    // Insert order into MySQL (linked to the logged-in buyer's account)
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, buyer_name, buyer_role, phone, total_amount, status, payment_method, shipping_address, batch_code, driver) 
                           VALUES (?, ?, ?, ?, ?, ?, 'Order Confirmed & QR Tagged', ?, ?, ?, 'Famox Logistics Lunas Hub (Van KDH 4410)')");
    $stmt->execute([$user['id'], $orderNum, $name, $user['role_name'] ?? 'Direct Consumer', $phone, $totalAmount, $paymentMethod, $address, $batchCode]);
    $orderId = $pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_name, quantity, unit, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $it) {
        $sub = $it['price'] * $it['qty'];
        $itemStmt->execute([$orderId, $it['name'], $it['qty'], $it['unit'], $it['price'], $sub]);
    }

    // Log Activity in MySQL
    $log = $pdo->prepare("INSERT INTO activity_logs (user_name, action, description, ip_address) VALUES (?, 'Order Placed & Paid', ?, ?)");
    $log->execute([$name, "Completed checkout for order $orderNum (RM " . number_format($totalAmount, 2) . ")", $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

    $orderSuccess = [
        'orderNum' => $orderNum,
        'name' => $name,
        'phone' => $phone,
        'total' => $totalAmount,
        'payment' => $paymentMethod,
        'batchCode' => $batchCode,
        'driver' => 'Famox Logistics Lunas Hub (Van KDH 4410)'
    ];
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-custom" style="padding: 40px 1.25rem 80px; max-width: 720px;">
    <?php if ($orderSuccess): ?>
        <!-- Order Confirmed Screen -->
        <div style="background: #ffffff; border-radius: 32px; padding: 40px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); text-align: center;">
            <div style="width: 64px; height: 64px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
                ✓
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Thank You for Supporting Kedah Farmers!</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 6px;">
                Your order <strong class="font-mono text-emerald"><?= htmlspecialchars($orderSuccess['orderNum']) ?></strong> has been confirmed with Famox Enterprise Lunas Hub.
            </p>

            <div style="background: #f9fafb; padding: 20px; border-radius: 20px; border: 1px solid #f3f4f6; margin: 24px 0; text-align: left; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Order Reference:</span>
                    <strong class="font-mono"><?= htmlspecialchars($orderSuccess['orderNum']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Customer:</span>
                    <strong><?= htmlspecialchars($orderSuccess['name']) ?> (<?= htmlspecialchars($orderSuccess['phone']) ?>)</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Assigned Logistics:</span>
                    <strong><?= htmlspecialchars($orderSuccess['driver']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #6b7280;">Batch Traceability Code:</span>
                    <strong class="font-mono text-emerald"><?= htmlspecialchars($orderSuccess['batchCode']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 14px;">
                    <span>Paid Amount:</span>
                    <strong style="color: #059669;">RM <?= number_format($orderSuccess['total'], 2) ?></strong>
                </div>
            </div>

            <script>
                // Clear cart from local storage on successful order
                localStorage.removeItem('af_cart');
            </script>

            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="index.php" class="btn-primary" style="padding: 12px 24px;">Return to Storefront</a>
                <a href="qr_verify.php?batch=<?= urlencode($orderSuccess['batchCode']) ?>" target="_blank" class="btn-secondary" style="padding: 12px 24px;">
                    📱 View QR Farm Passport
                </a>
            </div>
        </div>

    <?php elseif (!$user): ?>
        <!-- GUEST USER NOT SIGNED IN: MUST SIGN IN OR REGISTER FIRST -->
        <div style="background: #ffffff; border-radius: 32px; padding: 40px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); text-align: center;">
            <div style="width: 60px; height: 60px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                🔒
            </div>
            <span class="badge-pill">Account Required to Checkout</span>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827; margin-top: 8px;">Please Sign In or Register First</h1>
            <p style="font-size: 13px; color: #6b7280; max-width: 480px; margin: 8px auto 24px;">
                To generate your verified QR code invoice, assign cold-chain delivery in Kedah, and ensure farm-to-table traceability, please sign in or create an account.
            </p>

            <!-- Quick 1-Click Login for Lecturer Presentation -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 20px; padding: 18px; max-width: 480px; margin: 0 auto 24px; text-align: left;">
                <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; margin-bottom: 10px; text-align: center;">
                    ⚡ 1-Click Demo Login (Continue as Buyer)
                </div>
                <form method="POST" action="login.php" style="margin: 0;">
                    <input type="hidden" name="redirect" value="checkout.php">
                    <input type="hidden" name="demo_login" value="buyer">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 800; cursor: pointer; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                        <span>🛒 <strong>Sign In as Muhammad Aizat (Buyer)</strong></span>
                        <span style="font-size: 11px; color: #16a34a; font-weight: 800;">Continue &rarr;</span>
                    </button>
                </form>
            </div>

            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="login.php?redirect=checkout.php" class="btn-primary" style="padding: 12px 28px;">
                    Sign In with Password
                </a>
                <a href="register.php?redirect=checkout.php" class="btn-secondary" style="padding: 12px 28px;">
                    Register New Account
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- LOGGED IN USER: FULL CHECKOUT FORM -->
        <div style="background: #ffffff; border-radius: 32px; padding: 36px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg);">
            <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span class="badge-pill">Direct Farmer Checkout</span>
                    <h1 style="font-size: 24px; font-weight: 900; color: #111827; margin-top: 4px;">Delivery & Payment Details</h1>
                </div>
                <div style="font-size: 11px; color: #065f46; background: #ecfdf5; padding: 6px 12px; border-radius: 9999px; border: 1px solid #a7f3d0; font-weight: 700;">
                    ✓ Signed in: <?= htmlspecialchars($user['name']) ?>
                </div>
            </div>

            <form method="POST" action="checkout.php" onsubmit="prepareCheckoutSubmit(event)">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="cart_data" id="checkoutCartData">
                <input type="hidden" name="total_amount" id="checkoutTotalAmount" value="45.40">

                <div style="display: flex; flex-direction: column; gap: 16px; font-size: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name / Buyer Account</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="form-input" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Contact Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?: '+60 19-334 8812') ?>" required class="form-input" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Delivery Address (Kedah / Penang / Malaysia)</label>
                        <textarea name="address" rows="2" required class="form-input" style="width: 100%;"><?= htmlspecialchars($user['address'] ?: 'No. 12, Jalan Lunas Makmur 3, 09600 Lunas, Kedah') ?></textarea>
                    </div>

                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 8px;">Payment Gateway</label>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #d1fae5; background: #f0fdf4; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="FPX Online Banking (Maybank)" checked>
                                <span><strong>FPX Online Banking</strong> (Maybank2u, CIMB, Bank Islam)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="DuitNow QR Instant Pay">
                                <span><strong>DuitNow QR</strong> (Touch 'n Go eWallet, GrabPay, Boost)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="Credit/Debit Card (Visa/Mastercard)">
                                <span><strong>Credit / Debit Card</strong></span>
                            </label>
                        </div>
                    </div>

                    <div style="background: #f9fafb; padding: 16px; border-radius: 16px; border: 1px solid #f3f4f6; margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span>Bag Subtotal:</span>
                            <strong id="chkSubtotal">RM 0.00</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span>Delivery Fee:</span>
                            <strong class="text-emerald" id="chkDelivery">FREE</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 15px; border-top: 1px solid #e5e7eb; padding-top: 8px;">
                            <span>Total Payable:</span>
                            <strong style="color: #059669;" id="chkTotal">RM 0.00</strong>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 10px;">
                        <button type="submit" class="btn-primary" style="padding: 14px 40px; font-size: 14px; width: 100%; max-width: 360px; margin: 0 auto; display: inline-flex; justify-content: center; box-shadow: 0 4px 14px rgba(5,150,105,0.3);">
                            Confirm Order with Freshness Guarantee
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            function prepareCheckoutSubmit(e) {
                const cartData = localStorage.getItem('af_cart') || '[]';
                document.getElementById('checkoutCartData').value = cartData;
            }

            document.addEventListener('DOMContentLoaded', () => {
                const cart = JSON.parse(localStorage.getItem('af_cart')) || [];
                const sub = cart.reduce((s, i) => s + (i.price * i.qty), 0);
                const del = sub >= 30 || sub === 0 ? 0 : 5;
                const total = sub + del;

                document.getElementById('chkSubtotal').innerText = 'RM ' + sub.toFixed(2);
                document.getElementById('chkDelivery').innerText = del === 0 ? 'FREE' : 'RM 5.00';
                document.getElementById('chkTotal').innerText = 'RM ' + total.toFixed(2);
                document.getElementById('checkoutTotalAmount').value = total.toFixed(2);
            });
        </script>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
