<?php 
global $lang; 
$isAuth = isLoggedIn();
?>
</main>

<footer class="main-footer">
    <div class="container-custom">

        <div class="footer-notes">
            <p>1. AgriFresh Connect is an academic and industry web application developed as a Final Year Project for <strong>Bachelor of Science (Honours) Management Information Systems</strong>, Faculty of Business and Management, AIMST University (2026).</p>
            <p>2. Developed by <strong>Muhammad Aizat Izzuddin Bin Azmi (Student ID: B23101069)</strong> in collaboration with <strong>Famox Enterprise Sdn Bhd</strong>, Lunas, Kedah.</p>
            <p>3. Product traceability data is generated dynamically via QR codes and verified against Malaysian Good Agricultural Practices (MyGAP) certifications. Direct-to-consumer fair trade price calculations reflect local Kedah market data.</p>
        </div>


        <div class="footer-grid">
            <div>
                <h4>Explore Produce</h4>
                <ul>
                    <li><a href="index.php">Fresh Leafy Greens</a></li>
                    <li><a href="index.php">Roma Tomatoes & Fruiting</a></li>
                    <li><a href="index.php">Kulim Bird's Eye Chilies</a></li>
                    <li><a href="index.php">Baling Mountain Honey Corn</a></li>
                    <li><a href="index.php">Famox Weekly Harvest Boxes</a></li>
                </ul>
            </div>
            <div>
                <h4>QR Traceability</h4>
                <ul>
                    <li><a href="javascript:void(0)" onclick="openScannerModal()">Live QR Scanner</a></li>
                    <li><a href="qr_verify.php?batch=AF-LUN-2026-089">Sample Batch Passport</a></li>
                    <li><a href="index.php#traceability">Farm-to-Table Workflow</a></li>
                    <li><a href="index.php#impact">MyGAP Quality Grading</a></li>
                </ul>
            </div>
            <div>
                <h4>Famox Enterprise</h4>
                <ul>
                    <li><a href="index.php#famox">Lunas Central Aggregation Hub</a></li>
                    <li><a href="index.php#farmers">Kedah Supplier Network</a></li>
                    <li><a href="index.php#impact">Fair Trade Pricing Model</a></li>
                    <li><a href="admin_panel.php">Central Admin Oversight</a></li>
                </ul>
            </div>
            <div>
                <h4>Project & University</h4>
                <ul>
                    <li><strong>AIMST University</strong></li>
                    <li>Faculty of Business & Management</li>
                    <li>BSc (Hons) MIS FYP 2026</li>
                    <li>Candidate: Muhammad Aizat Izzuddin (B23101069)</li>
                </ul>
            </div>
        </div>


        <div class="footer-bottom">
            <div>&copy; 2026 AgriFresh Connect • Famox Enterprise Supplier Digital Marketplace. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Use</a>
                <a href="#">AIMST MIS Project Repository</a>
            </div>
        </div>
    </div>
</footer>

<div id="cartDrawerBackdrop" class="drawer-backdrop" onclick="closeCartDrawer()">
    <div class="cart-drawer" onclick="event.stopPropagation()">
        <div class="drawer-header">
            <div class="drawer-title">
                <svg class="icon-md text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <h3><?= $lang === 'en' ? 'Your Fresh Produce Bag' : 'Troli Hasil Segar Anda' ?></h3>
            </div>
            <button onclick="closeCartDrawer()" class="btn-close">&times;</button>
        </div>

        <div class="free-delivery-meter">
            <div class="meter-text">
                <span id="meterLabel">Add for Free Delivery</span>
                <span id="meterPercent">0%</span>
            </div>
            <div class="meter-bar">
                <div id="meterFill" class="meter-fill" style="width: 0%;"></div>
            </div>
        </div>

        <div id="cartItemsList" class="cart-items-wrap">

        </div>

        <div id="cartFooter" class="drawer-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cartSubtotalText">RM 0.00</span>
            </div>
            <div class="summary-row">
                <span>Estimated Kedah Delivery</span>
                <span id="cartDeliveryText" class="text-emerald">FREE</span>
            </div>
            <div class="summary-row total-row">
                <span>Total Amount</span>
                <span id="cartTotalText" class="total-price">RM 0.00</span>
            </div>
            
            <?php if ($isAuth): ?>
                <a href="checkout.php" class="btn-primary btn-block">
                    <span>Review Bag & Proceed to Checkout</span> &rarr;
                </a>
            <?php else: ?>
                <a href="login.php?redirect=checkout.php" class="btn-primary btn-block">
                    <span>🔒 Sign In / Register to Checkout</span> &rarr;
                </a>
                <div style="font-size: 10px; color: #6b7280; text-align: center; margin-top: 4px;">
                    Account required to save order & delivery in Kedah
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="scannerModalBackdrop" class="modal-backdrop" onclick="closeScannerModal()">
    <div class="modal-card" onclick="event.stopPropagation()">
        <button onclick="closeScannerModal()" class="btn-close-modal">&times;</button>
        <div class="text-center">
            <div class="modal-icon-badge">📷</div>
            <h3 class="modal-title"><?= $txt['scanTitle'] ?? 'Scan Farm Batch QR' ?></h3>
            <p class="modal-subtitle"><?= $txt['scanSub'] ?? 'Use your device camera to verify the farm-to-table traceability record.' ?></p>
        </div>

        <div class="scanner-viewfinder">
            <div class="laser-line"></div>
            <div class="qr-placeholder-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div id="scannerStatusText" class="scanner-status">Point device camera at crate QR code</div>
        </div>

        <div class="quick-batch-testers">
            <p class="test-label">Or Click to Test Active Dawn Harvest Batches:</p>
            <div class="test-pills">
                <button type="button" onclick="simulateScanBatch('AF-LUN-2026-089')" class="batch-pill">AF-LUN-2026-089 (Roma Tomato)</button>
                <button type="button" onclick="simulateScanBatch('AF-KLM-2026-042')" class="batch-pill">AF-KLM-2026-042 (Butterhead)</button>
                <button type="button" onclick="simulateScanBatch('AF-BLG-2026-115')" class="batch-pill">AF-BLG-2026-115 (Honey Corn)</button>
                <button type="button" onclick="simulateScanBatch('AF-FMX-2026-BOX1')" class="batch-pill">AF-FMX-2026-BOX1 (Weekly Box)</button>
            </div>
        </div>

        <form onsubmit="handleManualBatchSubmit(event)" class="manual-batch-form">
            <input type="text" id="manualBatchInput" placeholder="Or enter Batch ID (e.g. AF-LUN-2026-089)" class="form-input">
            <button type="submit" class="btn-dark">Verify</button>
        </form>
    </div>
</div>

<div id="qrModalBackdrop" class="modal-backdrop" onclick="closeQRModal()">
    <div class="modal-card modal-lg" onclick="event.stopPropagation()">
        <button onclick="closeQRModal()" class="btn-close-modal">&times;</button>
        <div class="modal-header-flex">
            <div class="modal-brand-thumb">
                <img src="assets/img/agrifresh_logo.png" alt="AgriFresh">
            </div>
            <div>
                <div class="badge-pill">AgriFresh QR Passport</div>
                <h3 id="qrModalTitle" class="modal-heading">Harvest Provenance</h3>
            </div>
        </div>

        <div class="qr-passport-grid">
            <div class="qr-canvas-box">
                <div id="qrCanvasContainer" class="canvas-render"></div>
                <div id="qrModalBatchTag" class="batch-code-tag">BATCH: AF-LUN-2026-089</div>
                <div class="verified-origin-pill">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>Famox Verified Origin</span>
                </div>
            </div>

            <div class="qr-details-box">
                <div class="farmer-mini-card">
                    <img
                        id="qrModalFarmerAvatar"
                        src="assets/img/agrifresh_logo.png"
                        alt="Farmer"
                        class="farmer-thumb"
                        onerror="this.onerror=null; this.src='assets/img/agrifresh_lo.png';"
                        >
                    <div>
                        <h4 id="qrModalFarmerName" class="farmer-name">Pak Cik Azman</h4>
                        <p id="qrModalFarmName" class="farm-name text-emerald">Ladang Hijau Makmur</p>
                        <p id="qrModalFarmLoc" class="farm-loc">Lunas, Kulim District, Kedah</p>
                    </div>
                </div>

                <div class="provenance-rows">
                    <div class="prov-row">
                        <span class="prov-label">Harvest Timestamp:</span>
                        <span id="qrModalHarvest" class="prov-val font-mono">2026-09-02 05:30 AM</span>
                    </div>
                    <div class="prov-row">
                        <span class="prov-label">Quality Standard:</span>
                        <span id="qrModalGrade" class="prov-val text-emerald font-bold">Grade A Premium</span>
                    </div>
                    <div class="prov-row">
                        <span class="prov-label">Pesticide Residue:</span>
                        <span id="qrModalPesticide" class="prov-val text-emerald">0.00 ppm (MyGAP Safe)</span>
                    </div>
                    <div class="prov-row">
                        <span class="prov-label">Storage Temperature:</span>
                        <span id="qrModalStorage" class="prov-val">4°C - 8°C Cold Chain</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-actions-flex">
            <button type="button" onclick="printQRSticker()" class="btn-emerald">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Print Crate QR Sticker</span>
            </button>
            <button type="button" onclick="closeQRModal()" class="btn-dark">Close</button>
        </div>
    </div>
</div>

<!-- Apology Pop-up Modal for Declined / Out of Stock Orders -->
<div id="apologyModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(5px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 540px; border-radius: 28px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); text-align: center; position: relative; border: 1.5px solid #fed7aa;">
        <div style="width: 72px; height: 72px; background: #fff7ed; border: 2px solid #fdba74; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 34px;">
            🌾
        </div>
        
        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 9999px; border: 1px solid #fecaca; display: inline-block; margin-bottom: 8px;">
            ⚠️ Fresh Produce Out of Stock Notice
        </span>

        <h3 style="font-size: 22px; font-weight: 900; color: #111827; margin-bottom: 8px;">We Sincerely Apologize!</h3>
        
        <p style="font-size: 13px; color: #4b5563; line-height: 1.6; margin-bottom: 18px;">
            We regret to inform you that order <strong id="apologyOrderNumber" style="color: #111827; font-family: monospace;">#ORDER</strong> could not be fulfilled because the fresh harvest is currently <strong>Out of Stock</strong>.
        </p>

        <!-- Farmer Reason Card -->
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 14px 18px; text-align: left; margin-bottom: 18px;">
            <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #92400e; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                <span>👨‍🌾 Farmer's Explanation:</span>
            </div>
            <div id="apologyReasonText" style="font-size: 13px; font-weight: 700; color: #78350f; font-style: italic;">
                "Harvest Out of Stock - Dawn crop depleted due to high demand"
            </div>
        </div>

        <!-- 100% Refund Guarantee Box -->
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 16px; padding: 14px 18px; text-align: left; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px;">
            <span style="font-size: 24px; line-height: 1;">💰</span>
            <div>
                <div style="font-size: 13px; font-weight: 800; color: #065f46;">
                    100% Full Refund Guarantee
                </div>
                <div style="font-size: 12px; color: #047857; margin-top: 2px;">
                    Your payment of <strong id="apologyRefundAmount">RM 0.00</strong> has been processed for automated reversal under the Famox Fresh Quality Guarantee.
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <button type="button" onclick="closeApologyModal()" class="btn-dark" style="padding: 11px 22px; font-size: 13px; font-weight: 700; cursor: pointer;">
                Understood / Close
            </button>
            <a href="index.php#store" class="btn-primary" style="padding: 11px 24px; font-size: 13px; font-weight: 800;">
                <span>🛒 Browse Other Fresh Harvests</span> &rarr;
            </a>
        </div>
    </div>
</div>

<div id="toastNotification" class="toast-popup">
    <span class="toast-icon">🌱</span>
    <span id="toastMessageText">Notification</span>
</div>

<script src="assets/js/app.js"></script>
<?php if (!empty($_SESSION['order_declined_alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showDeclinedOrderApology(
        "<?= htmlspecialchars(addslashes($_SESSION['order_declined_alert']['order_number'])) ?>",
        "<?= htmlspecialchars(addslashes($_SESSION['order_declined_alert']['reason'])) ?>",
        "RM <?= number_format($_SESSION['order_declined_alert']['total_amount'], 2) ?>"
    );
});
</script>
<?php unset($_SESSION['order_declined_alert']); ?>
<?php endif; ?>
</body>
</html>
