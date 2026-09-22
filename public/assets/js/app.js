// AgriFresh Connect Client Engine
// Cart, QR Generator, Scanner Simulator, & Modals

let cart = JSON.parse(localStorage.getItem('af_cart')) || [];
function saveCart() {
    localStorage.setItem('af_cart', JSON.stringify(cart));
    updateCartUI();
}

function updateCartUI() {
    const badge = document.getElementById('headerCartCount') || document.getElementById('cartCountBadge');
    const badgeSecondary = document.getElementById('cartCountBadge');
    const itemsList = document.getElementById('cartItemsList');
    const subtotalEl = document.getElementById('cartSubtotalText');
    const totalEl = document.getElementById('cartTotalText');
    const deliveryEl = document.getElementById('cartDeliveryText');
    const meterFill = document.getElementById('meterFill');
    const meterLabel = document.getElementById('meterLabel');
    const meterPercent = document.getElementById('meterPercent');

    const totalCount = cart.reduce((sum, item) => sum + item.qty, 0);
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const freeThreshold = 30.00;
    const isFreeDelivery = subtotal >= freeThreshold;
    const deliveryFee = isFreeDelivery ? 0 : 5.00;
    const grandTotal = subtotal + deliveryFee;

    if (badge) badge.innerText = totalCount;
    if (badgeSecondary) badgeSecondary.innerText = totalCount;

    if (itemsList) {
        if (cart.length === 0) {
            itemsList.innerHTML = `
                <div style="text-align:center; padding: 40px 0; color: #9ca3af;">
                    <div style="font-size: 36px; margin-bottom: 8px;">🥬</div>
                    <p style="font-weight: 700; color: #374151;">Your bag is empty.</p>
                    <p style="font-size: 12px; margin-top: 4px;">Explore our fresh morning harvests!</p>
                </div>
            `;
        } else {
            itemsList.innerHTML = cart.map(item => `
                <div class="cart-item-row">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-batch">Batch: ${item.batchId}</div>
                        <div class="cart-item-price">RM ${item.price.toFixed(2)} /${item.unit}</div>
                    </div>
                    <div class="qty-control">
                        <button type="button" class="btn-qty" onclick="changeQty(${item.id}, -1)">&minus;</button>
                        <span class="qty-num">${item.qty}</span>
                        <button type="button" class="btn-qty" onclick="changeQty(${item.id}, 1)">&plus;</button>
                    </div>
                    <button type="button" onclick="removeItem(${item.id})" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:16px;">&times;</button>
                </div>
            `).join('');
        }
    }

    if (subtotalEl) subtotalEl.innerText = 'RM ' + subtotal.toFixed(2);
    if (totalEl) totalEl.innerText = 'RM ' + grandTotal.toFixed(2);
    if (deliveryEl) deliveryEl.innerText = isFreeDelivery ? 'FREE' : 'RM 5.00';

    if (meterFill && meterLabel && meterPercent) {
        const pct = Math.min(100, Math.round((subtotal / freeThreshold) * 100));
        meterFill.style.width = pct + '%';
        meterPercent.innerText = pct + '%';
        if (isFreeDelivery) {
            meterLabel.innerText = '🎉 Free Delivery Qualified!';
        } else {
            meterLabel.innerText = 'Add RM ' + (freeThreshold - subtotal).toFixed(2) + ' for Free Delivery';
        }
    }
}

function addToCart(prod) {
    const existing = cart.find(i => i.id === prod.id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            id: prod.id,
            name: prod.name,
            price: parseFloat(prod.price),
            unit: prod.unit || 'kg',
            qty: 1,
            batchId: prod.batchId,
            image: prod.image
        });
    }
    saveCart();
    showToast(prod.name + ' added to Bag!');
}

function changeQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        saveCart();
    }
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    saveCart();
}

function openCartDrawer() {
    const drawer = document.getElementById('cartDrawerBackdrop');
    if (drawer) drawer.classList.add('show');
    updateCartUI();
}

function closeCartDrawer() {
    const drawer = document.getElementById('cartDrawerBackdrop');
    if (drawer) drawer.classList.remove('show');
}

function toggleRoleMenu(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const menu = document.getElementById('roleDropdownMenu') || document.getElementById('roleDropdown');
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

function toggleRoleDropdown(e) {
    toggleRoleMenu(e);
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown-wrap')) {
        const menu = document.getElementById('roleDropdownMenu') || document.getElementById('roleDropdown');
        if (menu) {
            menu.classList.remove('show');
            menu.style.setProperty('display', 'none', 'important');
        }
    }
});

function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    const txt = document.getElementById('toastMessageText');
    if (toast && txt) {
        txt.innerText = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}

// Robust QR Code Modal - Fetch directly from MySQL via API
async function openQRModalByBatch(batchId) {
    try {
        const res = await fetch('api/get_product_qr.php?batch=' + encodeURIComponent(batchId));
        const data = await res.json();
        
        if (data.success) {
            openQRModal(data);
        } else {
            // If API fails, redirect to qr_verify.php
            window.location.href = 'qr_verify.php?batch=' + encodeURIComponent(batchId);
        }
    } catch (err) {
        console.error('QR fetch error:', err);
        window.location.href = 'qr_verify.php?batch=' + encodeURIComponent(batchId);
    }
}

function openQRModal(data) {
    const modal = document.getElementById('qrModalBackdrop');
    if (!modal) return;

    document.getElementById('qrModalTitle').innerText = data.name || 'Harvest Provenance';
    document.getElementById('qrModalBatchTag').innerText = 'BATCH: ' + (data.batchId || 'AF-LUN-2026-089');
    document.getElementById('qrModalFarmerName').innerText = data.farmerName || 'Pak Cik Azman';
    document.getElementById('qrModalFarmName').innerText = data.farmName || 'Ladang Hijau Makmur';
    document.getElementById('qrModalFarmLoc').innerText = data.location || 'Lunas, Kedah';
    document.getElementById('qrModalHarvest').innerText = data.harvestDate || '2026-09-02 05:30 AM';
    document.getElementById('qrModalGrade').innerText = data.grade || 'Grade A Premium';
    document.getElementById('qrModalPesticide').innerText = data.pesticide || '0.00 ppm (MyGAP Safe)';
    document.getElementById('qrModalStorage').innerText = data.storage || '4°C - 8°C Cold Chain';

    const container = document.getElementById('qrCanvasContainer');
    if (container) {
        container.innerHTML = '';
        const verifyUrl = window.location.origin + window.location.pathname.replace(/[^/]*$/, '') + 'qr_verify.php?batch=' + data.batchId;
        
        // Render QR Code using image or SVG
        const img = document.createElement('img');
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=064e3b&data=' + encodeURIComponent(verifyUrl);
        img.alt = 'QR Code for ' + data.batchId;
        img.style.width = '140px';
        img.style.height = '140px';
        img.style.borderRadius = '8px';
        container.appendChild(img);
    }

    modal.classList.add('show');
}

function closeQRModal() {
    const modal = document.getElementById('qrModalBackdrop');
    if (modal) modal.classList.remove('show');
}

function printQRSticker() {
    window.print();
}

// Scanner Modal
function openScannerModal() {
    const modal = document.getElementById('scannerModalBackdrop');
    if (modal) modal.classList.add('show');
}

function closeScannerModal() {
    const modal = document.getElementById('scannerModalBackdrop');
    if (modal) modal.classList.remove('show');
}

function simulateScanBatch(batchId) {
    const statusText = document.getElementById('scannerStatusText');
    if (statusText) statusText.innerText = 'Decoding QR Code ' + batchId + '...';
    
    setTimeout(() => {
        closeScannerModal();
        window.location.href = 'qr_verify.php?batch=' + batchId;
    }, 700);
}

function handleManualBatchSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('manualBatchInput');
    if (input && input.value.trim()) {
        simulateScanBatch(input.value.trim());
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
});

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = btn.querySelector('.password-eye-icon');
    if (!icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.62 21.62 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
