<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'AgriFresh Connect | The Fresh Store for Kedah Smallholders' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/agrifresh_logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
    @stack('styles')
</head>
<body>
    @php
        $authUser = session('user');

        // Resolve user role mapping (handles numeric IDs 1, 2, 3 as well as text slugs)
        $userRole = '';
        if ($authUser) {
            $rawRole = $authUser['role'] ?? $authUser['user_role'] ?? $authUser['type'] ?? $authUser['role_id'] ?? $authUser['role_slug'] ?? $authUser['role_name'] ?? '';

            if ($rawRole === 1 || $rawRole === '1') $rawRole = 'admin';
            if ($rawRole === 2 || $rawRole === '2') $rawRole = 'farmer';
            if ($rawRole === 3 || $rawRole === '3') $rawRole = 'buyer';

            $userRole = strtolower(trim((string)$rawRole));
        }

        // Determine destination dashboard URL & role title
        $userPortalUrl = route('farmer.dashboard');
        $displayRole = __('Farmer');
        $userPortalTitle = __('Farmer Portal - Manage Produce & Profile');

        if ($authUser) {
            if (str_contains($userRole, 'admin')) {
                $userPortalUrl = route('admin.panel');
                $displayRole = __('Admin');
                $userPortalTitle = __('Famox Admin Hub');
            } elseif (str_contains($userRole, 'buyer')) {
                $userPortalUrl = route('buyer.dashboard');
                $displayRole = __('Buyer');
                $userPortalTitle = __('Buyer Portal - Orders, 4-Stage Delivery & Profile');
            }
        }

        // Clean any pre-existing duplicate '(Buyer)' text stored inside user name string
        $cleanUserName = $authUser ? trim(preg_replace('/\s*\((?:Buyer|Farmer|Admin)\)/i', '', $authUser['name'] ?? 'User')) : '';
        $cleanUserName = $cleanUserName ? explode(' ', $cleanUserName)[0] : '';
        
        $currentLang = app()->getLocale();
        $targetLang = $currentLang === 'ms' ? 'en' : 'ms';
        $langBadge = $currentLang === 'ms' ? 'BM' : 'EN';
    @endphp

    <!-- Promo Announcement Ribbon -->
    <div class="promo-ribbon">
        <div class="container-custom ribbon-flex">
            <span class="ribbon-brand">⭐ {{ __('Famox Enterprise Supplier Network') }}</span>
            <span class="ribbon-text">• {{ __('100% QR-Traceable Kedah Smallholder Harvests') }} •</span>
            <span class="ribbon-badge">{{ __('Use code AGRIFRESH for RM10 Off') }}</span>
        </div>
    </div>

    <!-- Glass Navigation Header -->
    <header class="glass-header">
    <div class="container-custom header-inner">

        <a href="index.php" class="brand-logo-wrap">
            <div class="brand-logo-img">
                <img src="{{ asset('assets/img/agrifresh_logo.png') }}" alt="AgriFresh Logo" style="width: 38px; height: 38px; object-fit: contain;">
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

            <div class="nav-actions">
                <button type="button" onclick="openScannerModal()" class="btn-scanner">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>{{ __('Scan QR') }}</span>
                </button>

                <!-- Language Toggle Switch -->
                <a href="{{ request()->fullUrlWithQuery(['lang' => $targetLang]) }}" class="lang-toggle-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 9999px; background: rgba(0,0,0,0.04); border: 1px solid #e5e7eb; color: #374151; font-size: 11px; font-weight: 700;">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <span>{{ $langBadge }}</span>
                </a>

                @if($authUser)
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="{{ $userPortalUrl }}" title="{{ $userPortalTitle }}" class="btn-header-user">
                            <span>👤 {{ $cleanUserName }} ({{ $displayRole }})</span>
                            <span class="user-arrow">&rarr;</span>
                        </a>
                    </div>
                @else
                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                        <a href="{{ route('login') }}" class="btn-header-auth">{{ __('Sign In') }}</a>
                        <a href="{{ route('register') }}" class="btn-header-outline">{{ __('Register') }}</a>
                    </div>
                @endif

                <!-- Role Access Dropdown -->
                <div class="dropdown-wrap" style="position: relative;">
                    <button type="button" class="btn-role-pill" onclick="toggleRoleMenu(event)">
                        <span>● {{ __('Role Access') }}</span>
                        <svg class="icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="roleDropdownMenu" class="dropdown-menu">
                        <div class="dropdown-title">{{ __('System Portals') }}</div>
                        <a href="{{ route('store.index') }}" class="dropdown-item">
                            <span>🛒</span>
                            <div>
                                <div class="item-head">{{ __('Buyer Storefront') }}</div>
                                <div class="item-desc">{{ __('Browse & Buy Kedah Produce') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('buyer.dashboard') }}" class="dropdown-item">
                            <span>📦</span>
                            <div>
                                <div class="item-head">{{ __('Buyer Dashboard') }}</div>
                                <div class="item-desc">{{ __('Track Orders, 4-Stage Delivery & Profile') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('farmer.dashboard') }}" class="dropdown-item">
                            <span>👨‍🌾</span>
                            <div>
                                <div class="item-head">{{ __('Farmer Portal (Famox)') }}</div>
                                <div class="item-desc">{{ __('Add, Edit & Remove Produce') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.panel') }}" class="dropdown-item">
                            <span>🏢</span>
                            <div>
                                <div class="item-head">{{ __('Famox Admin Panel') }}</div>
                                <div class="item-desc">{{ __('Hub & Quality Oversight') }}</div>
                            </div>
                        </a>

                        <div style="border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 6px;">
                            @if($authUser)
                                <a href="{{ route('logout') }}" class="dropdown-item" style="color: #dc2626;">
                                    <span>🚪</span>
                                    <div>
                                        <div class="item-head">{{ __('Sign Out') }} ({{ $cleanUserName }})</div>
                                        <div class="item-desc">{{ __('Log out of account') }}</div>
                                    </div>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="dropdown-item">
                                    <span>🔑</span>
                                    <div>
                                        <div class="item-head">{{ __('Sign In') }}</div>
                                        <div class="item-desc">{{ __('1-Click Demo or Password') }}</div>
                                    </div>
                                </a>
                                <a href="{{ route('register') }}" class="dropdown-item">
                                    <span>📝</span>
                                    <div>
                                        <div class="item-head">{{ __('Register') }}</div>
                                        <div class="item-desc">{{ __('Register Buyer / Farmer') }}</div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <button type="button" onclick="openCartDrawer()" class="btn-bag" aria-label="Cart Bag">
                    <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span id="headerCartCount" class="bag-badge">0</span>
                </button>
            </div>
        </div>
    </div>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="container-custom">
            <div class="footer-notes">
                <p>1. AgriFresh Connect is an academic and industry web application developed as a Final Year Project for <strong>Bachelor of Science (Honours) Management Information Systems</strong>, Faculty of Business and Management, AIMST University (2026).</p>
                <p>2. Developed by <strong>Muhammad Aizat Izzuddin Bin Azmi (Student ID: B23101069)</strong> in collaboration with <strong>Famox Enterprise Sdn Bhd</strong>, Lunas, Kedah.</p>
                <p>3. Product traceability data is generated dynamically via QR codes and verified against Malaysian Good Agricultural Practices (MyGAP) certifications. Direct-to-consumer fair trade price calculations reflect local Kedah market data.</p>
            </div>

            <div class="footer-grid">
                <div>
                    <h4>{{ __('Explore Produce') }}</h4>
                    <ul>
                        <li><a href="{{ route('store.index') }}">{{ __('Fresh Leafy Greens') }}</a></li>
                        <li><a href="{{ route('store.index') }}">{{ __('Roma Tomatoes & Fruiting') }}</a></li>
                        <li><a href="{{ route('store.index') }}">{{ __('Kulim Bird\'s Eye Chilies') }}</a></li>
                        <li><a href="{{ route('store.index') }}">{{ __('Baling Mountain Honey Corn') }}</a></li>
                        <li><a href="{{ route('store.index') }}">{{ __('Famox Weekly Harvest Boxes') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('QR Traceability') }}</h4>
                    <ul>
                        <li><a href="javascript:void(0)" onclick="openScannerModal()">{{ __('Live QR Scanner') }}</a></li>
                        <li><a href="{{ route('qr.verify', ['batch' => 'AF-LUN-2026-089']) }}">{{ __('Sample Batch Passport') }}</a></li>
                        <li><a href="{{ route('store.index') }}#traceability">{{ __('Farm-to-Table Workflow') }}</a></li>
                        <li><a href="{{ route('store.index') }}#impact">{{ __('MyGAP Quality Grading') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('Famox Enterprise') }}</h4>
                    <ul>
                        <li><a href="{{ route('store.index') }}#famox">{{ __('Lunas Central Aggregation Hub') }}</a></li>
                        <li><a href="{{ route('store.index') }}#farmers">{{ __('Kedah Supplier Network') }}</a></li>
                        <li><a href="{{ route('store.index') }}#impact">{{ __('Fair Trade Pricing Model') }}</a></li>
                        <li><a href="{{ route('admin.panel') }}">{{ __('Central Admin Oversight') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4>{{ __('Project & University') }}</h4>
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

    <!-- Cart Drawer -->
    <div id="cartDrawerBackdrop" class="drawer-backdrop" onclick="closeCartDrawer()">
        <div class="cart-drawer" onclick="event.stopPropagation()">
            <div class="drawer-header">
                <div class="drawer-title">
                    <svg class="icon-md text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <h3>{{ $currentLang === 'en' ? 'Your Fresh Produce Bag' : 'Troli Hasil Segar Anda' }}</h3>
                </div>
                <button onclick="closeCartDrawer()" class="btn-close">&times;</button>
            </div>

            <div class="free-delivery-meter">
                <div class="meter-text">
                    <span id="meterLabel">{{ __('Add for Free Delivery') }}</span>
                    <span id="meterPercent">0%</span>
                </div>
                <div class="meter-bar">
                    <div id="meterFill" class="meter-fill" style="width: 0%;"></div>
                </div>
            </div>

            <div id="cartItemsList" class="cart-items-wrap"></div>

            <div id="cartFooter" class="drawer-footer">
                <div class="summary-row">
                    <span>{{ __('Subtotal') }}</span>
                    <span id="cartSubtotalText">RM 0.00</span>
                </div>
                <div class="summary-row">
                    <span>{{ __('Estimated Kedah Delivery') }}</span>
                    <span id="cartDeliveryText" class="text-emerald">{{ __('FREE') }}</span>
                </div>
                <div class="summary-row total-row">
                    <span>{{ __('Total Amount') }}</span>
                    <span id="cartTotalText" class="total-price">RM 0.00</span>
                </div>
                
                @if($authUser)
                    <a href="{{ route('checkout.index') }}" class="btn-primary btn-block">
                        <span>{{ __('Review Bag & Proceed to Checkout') }}</span> &rarr;
                    </a>
                @else
                    <a href="{{ route('login', ['redirect' => 'checkout.php']) }}" class="btn-primary btn-block">
                        <span>🔒 {{ __('Sign In / Register to Checkout') }}</span> &rarr;
                    </a>
                    <div style="font-size: 10px; color: #6b7280; text-align: center; margin-top: 4px;">
                        {{ __('Account required to save order & delivery in Kedah') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Live QR Scanner Modal -->
    <div id="scannerModalBackdrop" class="modal-backdrop" onclick="closeScannerModal()">
        <div class="modal-card" onclick="event.stopPropagation()">
            <button onclick="closeScannerModal()" class="btn-close-modal">&times;</button>
            <div class="text-center">
                <div class="modal-icon-badge">📷</div>
                <h3 class="modal-title">{{ $txt['scanTitle'] ?? __('Scan Farm Batch QR') }}</h3>
                <p class="modal-subtitle">{{ $txt['scanSub'] ?? __('Use your device camera to verify the farm-to-table traceability record.') }}</p>
            </div>

            <div class="scanner-viewfinder">
                <div class="laser-line"></div>
                <div class="qr-placeholder-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
                <div id="scannerStatusText" class="scanner-status">{{ __('Point device camera at crate QR code') }}</div>
            </div>

            <div class="quick-batch-testers">
                <p class="test-label">{{ __('Or Click to Test Active Dawn Harvest Batches:') }}</p>
                <div class="test-pills">
                    <button type="button" onclick="simulateScanBatch('AF-LUN-2026-089')" class="batch-pill">AF-LUN-2026-089 (Roma Tomato)</button>
                    <button type="button" onclick="simulateScanBatch('AF-KLM-2026-042')" class="batch-pill">AF-KLM-2026-042 (Butterhead)</button>
                    <button type="button" onclick="simulateScanBatch('AF-BLG-2026-115')" class="batch-pill">AF-BLG-2026-115 (Honey Corn)</button>
                    <button type="button" onclick="simulateScanBatch('AF-FMX-2026-BOX1')" class="batch-pill">AF-FMX-2026-BOX1 (Weekly Box)</button>
                </div>
            </div>

            <form onsubmit="handleManualBatchSubmit(event)" class="manual-batch-form">
                <input type="text" id="manualBatchInput" placeholder="{{ __('Or enter Batch ID (e.g. AF-LUN-2026-089)') }}" class="form-input">
                <button type="submit" class="btn-dark">{{ __('Verify') }}</button>
            </form>
        </div>
    </div>

    <!-- QR Modal -->
    <div id="qrModalBackdrop" class="modal-backdrop" onclick="closeQRModal()">
        <div class="modal-card modal-lg" onclick="event.stopPropagation()">
            <button onclick="closeQRModal()" class="btn-close-modal">&times;</button>
            <div class="modal-header-flex">
                <div class="modal-brand-thumb">
                    <img src="{{ asset('assets/img/agrifresh_logo.png') }}" alt="AgriFresh">
                </div>
                <div>
                    <div class="badge-pill">AgriFresh QR Passport</div>
                    <h3 id="qrModalTitle" class="modal-heading">{{ __('Harvest Provenance') }}</h3>
                </div>
            </div>

            <div class="qr-passport-grid">
                <div class="qr-canvas-box">
                    <div id="qrCanvasContainer" class="canvas-render"></div>
                    <div id="qrModalBatchTag" class="batch-code-tag">BATCH: AF-LUN-2026-089</div>
                    <div class="verified-origin-pill">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span>{{ __('Famox Verified Origin') }}</span>
                    </div>
                </div>

                <div class="qr-details-box">
                    <div class="farmer-mini-card">
                        <img id="qrModalFarmerImg" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300" alt="Farmer" class="farmer-thumb">
                        <div>
                            <h4 id="qrModalFarmerName" class="farmer-name">Pak Cik Azman</h4>
                            <p id="qrModalFarmName" class="farm-name text-emerald">Ladang Hijau Makmur</p>
                            <p id="qrModalFarmLoc" class="farm-loc">Lunas, Kulim District, Kedah</p>
                        </div>
                    </div>

                    <div class="provenance-rows">
                        <div class="prov-row">
                            <span class="prov-label">{{ __('Harvest Timestamp:') }}</span>
                            <span id="qrModalHarvest" class="prov-val font-mono">2026-09-02 05:30 AM</span>
                        </div>
                        <div class="prov-row">
                            <span class="prov-label">{{ __('Quality Standard:') }}</span>
                            <span id="qrModalGrade" class="prov-val text-emerald font-bold">Grade A Premium</span>
                        </div>
                        <div class="prov-row">
                            <span class="prov-label">{{ __('Pesticide Residue:') }}</span>
                            <span id="qrModalPesticide" class="prov-val text-emerald">0.00 ppm (MyGAP Safe)</span>
                        </div>
                        <div class="prov-row">
                            <span class="prov-label">{{ __('Storage Temperature:') }}</span>
                            <span id="qrModalStorage" class="prov-val">4°C - 8°C Cold Chain</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions-flex">
                <button type="button" onclick="printQRSticker()" class="btn-emerald">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>{{ __('Print Crate QR Sticker') }}</span>
                </button>
                <button type="button" onclick="closeQRModal()" class="btn-dark">{{ __('Close') }}</button>
            </div>
        </div>
    </div>

    <div id="toastNotification" class="toast-popup">
        <span class="toast-icon">🌱</span>
        <span id="toastMessageText">Notification</span>
    </div>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
    function toggleRoleMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const menu = document.getElementById('roleDropdownMenu');
        if (!menu) return;
        const isHidden = window.getComputedStyle(menu).display === 'none';
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
            const menu = document.getElementById('roleDropdownMenu');
            if (menu) {
                menu.classList.remove('show');
                menu.style.setProperty('display', 'none', 'important');
            }
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
