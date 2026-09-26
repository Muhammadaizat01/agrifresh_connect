@extends('layouts.app')

@section('title', 'Buyer Dashboard | AgriFresh Connect')

@push('styles')
<style>
.oc-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 800; margin-top: 4px; }
.pill-1 { background: #dbeafe; color: #1d4ed8; }
.pill-2 { background: #fef3c7; color: #d97706; }
.pill-3 { background: #ede9fe; color: #7c3aed; }
.pill-4 { background: #dcfce7; color: #059669; }

.stage-labels { display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; font-weight: 700; margin-bottom: 4px; }
.stage-bar { display: flex; align-items: center; }
.stage-dot { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; flex-shrink: 0; border: 2px solid #e5e7eb; background: #f3f4f6; color: #9ca3af; }
.stage-dot.done   { background: #059669; border-color: #059669; color: #fff; }
.stage-dot.active { background: #f59e0b; border-color: #d97706; color: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,0.25); }
.stage-line      { flex: 1; height: 3px; background: #e5e7eb; }
.stage-line.done { background: #059669; }
</style>
@endpush

@section('content')
@php
    $userName = $profile->name ?? $user['name'] ?? 'Buyer';
    $userEmail = $profile->email ?? $user['email'] ?? '';
    $userPhone = $profile->phone ?? $user['phone'] ?? '';
    $userAddress = $profile->address ?? $user['address'] ?? '';
    $userAvatar = $profile->avatar ?? $user['avatar'] ?? '';
    $displayAvatar = $userAvatar ? asset($userAvatar) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=1d4ed8&color=fff&size=150';

    function getOrderStage(string $status): int {
        $status = strtolower($status);
        if (str_contains($status, 'confirmed') || str_contains($status, 'placed') || str_contains($status, 'tagged')) return 1;
        if (str_contains($status, 'packed') || str_contains($status, 'hub') || str_contains($status, 'processing')) return 2;
        if (str_contains($status, 'transit') || str_contains($status, 'delivery') || str_contains($status, 'out')) return 3;
        if (str_contains($status, 'delivered') || str_contains($status, 'qr scanned')) return 4;
        return 1;
    }
@endphp

<div class="container-custom" style="padding: 30px 1.25rem 60px;">

    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 28px; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="position: relative; cursor: pointer;" onclick="openProfileModal()" title="Click to change profile photo">
                <img src="{{ $displayAvatar }}" alt="{{ $userName }}" style="width: 70px; height: 70px; border-radius: 22px; object-fit: cover; border: 3px solid #1d4ed8; box-shadow: 0 4px 10px rgba(29,78,216,0.2);">
                <div style="position: absolute; bottom: -4px; right: -4px; background: #111827; color: #ffffff; border: 2px solid #ffffff; border-radius: 50%; width: 26px; height: 26px; font-size: 11px; display: flex; align-items: center; justify-content: center;">
                    📷
                </div>
            </div>
            <div>
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #bfdbfe; color: #1e3a8a; padding: 2px 8px; border-radius: 4px;">
                    Verified Buyer Account
                </span>
                <h1 style="font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px;">👋 Hi, {{ $userName }}!</h1>
                <p style="font-size: 12px; color: #4b5563;">
                    {{ $userEmail }}{{ $userPhone ? ' • ' . $userPhone : '' }}
                </p>
                @if ($userAddress)
                <p style="font-size: 11px; color: #1d4ed8; margin-top: 2px;">
                    📍 {{ $userAddress }}
                </p>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="openProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                <span>📷 Edit Profile & Photo</span>
            </button>
            <button type="button" onclick="openPasswordModal()" class="btn-secondary" style="padding: 10px 18px; font-size: 12px;">
                🔐 Change Password
            </button>
            <a href="{{ route('store.index') }}" class="btn-primary" style="padding: 10px 18px; font-size: 12px;">
                <span>Continue Shopping</span> &rarr;
            </a>
        </div>
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; color: #065f46; padding: 18px 24px; border-radius: 20px; border: 1px solid #a7f3d0; margin-bottom: 28px; font-size: 13px; font-weight: 700;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: 16px; border: 1px solid #fca5a5; margin-bottom: 24px; font-size: 13px;">
            ❌ {{ $errors->first() }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 30px;">
        <div style="background: #ffffff; padding: 18px 20px; border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <span style="font-size: 24px; margin-bottom: 6px; display: block;">📦</span>
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-bottom: 4px;">Total Orders</div>
            <div style="font-size: 22px; font-weight: 800; color: #111827;">{{ count($orders) }}</div>
        </div>
        <div style="background: #ffffff; padding: 18px 20px; border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <span style="font-size: 24px; margin-bottom: 6px; display: block;">🚚</span>
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-bottom: 4px;">Active Deliveries</div>
            <div style="font-size: 22px; font-weight: 800; color: #111827;">{{ $activeCount }}</div>
        </div>
        <div style="background: #ffffff; padding: 18px 20px; border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <span style="font-size: 24px; margin-bottom: 6px; display: block;">💰</span>
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-bottom: 4px;">Total Spent</div>
            <div style="font-size: 22px; font-weight: 800; color: #111827;">RM {{ number_format($totalSpent, 2) }}</div>
        </div>
    </div>

    <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800;">📦 Order History & 4-Stage Delivery Tracker</h2>
                <p style="font-size: 12px; color: #6b7280;">Follow every order from Famox Hub to your doorstep in real time.</p>
            </div>
            <a href="{{ route('store.index') }}" style="font-size: 12px; color: #1d4ed8; font-weight: 700;">Browse Fresh Produce &rarr;</a>
        </div>

        @if (empty($orders))
            <div style="text-align:center;padding:40px 0;color:#6b7280;">
                <div style="font-size:44px;margin-bottom:12px;">🛒</div>
                <div style="font-weight:700;margin-bottom:8px;">No orders yet!</div>
                <a href="{{ route('store.index') }}" style="color:#1d4ed8;font-weight:700;">Browse Fresh Produce →</a>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 14px;">
            @foreach ($orders as $order)
                @php
                    $orderObj = (object)$order;
                    $isDeclined = str_contains(strtolower($orderObj->status ?? ''), 'decline');
                    $stage = getOrderStage($orderObj->status ?? '');
                    $stageLabels = ['1. Order Placed', '2. Packed at Hub', '3. Out for Delivery', '4. Delivered ✅'];
                    $pillClass = 'pill-' . $stage;
                @endphp
                <div style="padding: 18px 20px; background: #f9fafb; border-radius: 18px; border: 1.5px solid <?= $isDeclined ? '#fca5a5' : '#f3f4f6' ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #111827;">{{ $orderObj->order_number ?? 'ORD-' . $orderObj->id }}</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">📅 {{ date('d M Y, h:i A', strtotime($orderObj->created_at ?? 'now')) }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size: 16px; font-weight: 800; color: {{ $isDeclined ? '#dc2626' : '#1d4ed8' }};">RM {{ number_format($orderObj->total_amount ?? 0, 2) }}</div>
                            @if($isDeclined)
                                <span class="oc-pill" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;">❌ {{ $orderObj->status ?? 'Declined (Out of Stock)' }}</span>
                            @else
                                <span class="oc-pill {{ $pillClass }}">{{ $orderObj->status ?? 'Placed' }}</span>
                            @endif
                        </div>
                    </div>

                    @if($isDeclined)
                        <!-- Out of Stock Apology & Refund Box -->
                        <div style="background: #fff1f2; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin: 10px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <div style="font-weight: 800; color: #991b1b; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                        <span>⚠️ Out of Stock Apology from Farmer</span>
                                    </div>
                                    <div style="font-size: 12px; color: #7f1d1d; margin-top: 4px; font-style: italic;">
                                        "{{ $orderObj->notes ?? 'Harvest Out of Stock - Dawn crop depleted due to high demand' }}"
                                    </div>
                                    <div style="font-size: 11px; color: #065f46; font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                                        <span>💰 100% Full Refund Processed (RM {{ number_format($orderObj->total_amount ?? 0, 2) }}) under Famox Guarantee</span>
                                    </div>
                                </div>
                                <button type="button" onclick="showDeclinedOrderApology('{{ $orderObj->order_number ?? '#' . $orderObj->id }}', '{{ addslashes($orderObj->notes ?? 'Harvest Out of Stock') }}', 'RM {{ number_format($orderObj->total_amount ?? 0, 2) }}')" class="btn-dark" style="padding: 7px 14px; font-size: 11px; cursor: pointer;">
                                    View Apology Details
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="stage-labels">
                            @foreach ($stageLabels as $i => $lbl)
                                @if ($i + 1 === $stage)
                                    <span style="color:#d97706;font-weight:800;">{{ $lbl }}</span>
                                @elseif ($i + 1 < $stage)
                                    <span style="color:#059669;">{{ $lbl }}</span>
                                @else
                                    <span>{{ $lbl }}</span>
                                @endif
                            @endforeach
                        </div>
                        <div class="stage-bar">
                            @for ($i = 1; $i <= 4; $i++)
                                @php
                                    $dc = ($i < $stage) ? 'done' : (($i === $stage) ? 'active' : '');
                                    $lc = ($i > 1 && ($i - 1) < $stage) ? 'done' : '';
                                @endphp
                                @if ($i > 1)<div class="stage-line {{ $lc }}"></div>@endif
                                <div class="stage-dot {{ $dc }}">{{ ($dc === 'done') ? '✓' : $i }}</div>
                            @endfor
                        </div>
                    @endif

                    <div style="font-size:11px;color:#6b7280;margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                        <span>📍 {{ $orderObj->shipping_address ?? 'N/A' }}</span>
                        @if (!empty($orderObj->driver))<span> | 🚚 {{ $orderObj->driver }}</span>@endif
                        @if (!empty($orderObj->payment_method))<span> | 💳 {{ $orderObj->payment_method }}</span>@endif
                    </div>
                </div>
            @endforeach
            </div>
        @endif
    </div>
</div>

<div id="profileModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📷</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Update Profile & Photo</h3>
                    <p style="font-size: 11px; color: #6b7280;">Change your avatar, contact details, and delivery address.</p>
                </div>
            </div>
            <button type="button" onclick="closeProfileModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="profileForm" action="{{ route('buyer.updateProfile') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf
            <input type="hidden" name="action" value="update_profile">

            <div style="text-align: center; padding: 16px; background: #f9fafb; border-radius: 20px; border: 1px solid #e5e7eb;">
                <img id="avatarModalPreview" src="{{ $displayAvatar }}" alt="Avatar Preview" style="width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #1d4ed8; margin: 0 auto 10px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="btn-primary" style="padding: 6px 14px; font-size: 11px; margin: 0 auto; cursor: pointer;">
                    📁 Choose Photo from Device
                </button>
                <input type="file" name="avatar_file" id="avatarFileInput" accept="image/*" style="display: none;" onchange="previewAvatarImage(this)">
                <div id="avatarFileNameDisplay" style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 6px;"></div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name</label>
                <input type="text" name="name" value="{{ $userName }}" required class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                    <input type="email" name="email" value="{{ $userEmail }}" required class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Phone Number</label>
                    <input type="text" name="phone" value="{{ $userPhone }}" class="form-input" style="width: 100%;" placeholder="+60 12-345 6789">
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Delivery Address</label>
                <textarea name="address" rows="2" class="form-input" style="width: 100%;">{{ $userAddress }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" id="profileSubmitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">💾 Save Profile & Photo</button>
            </div>
        </form>
    </div>
</div>

<div id="passwordModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 460px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🔐</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Change Password</h3>
                    <p style="font-size: 11px; color: #6b7280;">Keep your buyer account secure.</p>
                </div>
            </div>
            <button type="button" onclick="closePasswordModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('buyer.updatePassword') }}" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf
            <input type="hidden" name="action" value="update_password">
            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Current Password</label>
                <input type="password" name="current_password" required class="form-input" style="width: 100%;" placeholder="Enter current password">
            </div>
            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">New Password</label>
                <input type="password" name="new_password" required minlength="8" class="form-input" style="width: 100%;" placeholder="Min 8 characters">
            </div>
            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" required class="form-input" style="width: 100%;" placeholder="Repeat new password">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closePasswordModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">🔑 Update Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'flex';
}
function closeProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'none';
}
function openPasswordModal() {
    const modal = document.getElementById('passwordModalBackdrop');
    if (modal) modal.style.display = 'flex';
}
function closePasswordModal() {
    const modal = document.getElementById('passwordModalBackdrop');
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

window.addEventListener('click', function(e) {
    const profileModal = document.getElementById('profileModalBackdrop');
    const passwordModal = document.getElementById('passwordModalBackdrop');
    if (e.target === profileModal) profileModal.style.display = 'none';
    if (e.target === passwordModal) passwordModal.style.display = 'none';
});

document.addEventListener('DOMContentLoaded', function () {
    const pForm = document.getElementById('profileForm');
    if (pForm) {
        pForm.addEventListener('submit', function () {
            const btn = document.getElementById('profileSubmitBtn');
            if (btn) {
                btn.innerHTML = '⏳ Saving Profile...';
                btn.style.opacity = '0.75';
            }
        });
    }
});
</script>
@endpush