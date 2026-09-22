@extends('layouts.app')

@section('title', 'Account Settings | AgriFresh Connect')

@section('content')
@php
    $userName    = $profile->name ?? $user['name'] ?? 'Buyer';
    $userEmail   = $profile->email ?? $user['email'] ?? '';
    $userPhone   = $profile->phone ?? $user['phone'] ?? '';
    $userAddress = $profile->address ?? $user['address'] ?? '';
    $userAvatar  = $profile->avatar ?? $user['avatar'] ?? '';
    $displayAvatar = $userAvatar ? asset($userAvatar) : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=10b981&color=fff&size=150';
@endphp

<div class="container-custom" style="padding: 30px 1.25rem 60px; max-width: 900px;">

    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: #111827; margin: 0;">⚙️ Account Settings</h1>
            <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0;">Manage your buyer personal profile, delivery address, and security credentials.</p>
        </div>
        <a href="{{ route('buyer.dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #ecfdf5; border: 1.5px solid #10b981; border-radius: 10px; color: #065f46; font-size: 13px; font-weight: 700; text-decoration: none;">
            ← Back to Dashboard
        </a>
    </div>

    @if (session('success'))
        <div style="background: #dcfce7; color: #065f46; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Details & Picture Form -->
    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #e5e7eb; padding: 26px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <h2 style="font-size: 17px; font-weight: 800; color: #111827; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px;">
            👤 Personal Details & Profile Photo
        </h2>

        <form action="{{ route('buyer.updateProfile') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Avatar Preview & Upload -->
            <div style="display: flex; align-items: center; gap: 20px; padding: 16px; background: #f9fafb; border-radius: 16px; border: 1px solid #e5e7eb; margin-bottom: 20px;">
                <img id="avatarPreview" src="{{ $displayAvatar }}" alt="Profile Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                <div>
                    <div style="font-size: 13px; font-weight: 800; color: #111827; margin-bottom: 6px;">Profile Photo</div>
                    <button type="button" onclick="document.getElementById('avatarInput').click()" style="background: #ffffff; border: 1.5px solid #d1d5db; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; color: #374151; cursor: pointer; transition: 0.15s;">
                        📁 Choose New Photo
                    </button>
                    <input type="file" id="avatarInput" name="avatar_file" accept="image/*" style="display: none;" onchange="previewImage(event)">
                    <div id="avatarFileName" style="font-size: 11px; color: #059669; margin-top: 5px; font-weight: 600;"></div>
                    <p style="font-size: 11px; color: #9ca3af; margin: 4px 0 0;">JPG, PNG, or WEBP. Max 2MB.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $userName) }}" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $userEmail) }}" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $userPhone) }}" placeholder="+60 12-345 6789" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Default Delivery Address</label>
                    <textarea name="address" rows="2" class="form-control" placeholder="House number, street, city..." style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px; resize: vertical;">{{ old('address', $userAddress) }}</textarea>
                </div>
            </div>

            <div style="text-align: right;">
                <button type="submit" style="background: #059669; color: #ffffff; border: none; padding: 11px 26px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.15s;">
                    💾 Save Profile Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Password Change Form -->
    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #e5e7eb; padding: 26px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <h2 style="font-size: 17px; font-weight: 800; color: #111827; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px;">
            🔐 Change Password
        </h2>

        <form action="{{ route('buyer.updatePassword') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr; gap: 16px; max-width: 550px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Current Password</label>
                    <input type="password" name="current_password" required placeholder="Enter existing password" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">New Password</label>
                        <input type="password" name="new_password" required minlength="8" placeholder="At least 8 characters" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px;">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required placeholder="Repeat new password" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 13px;">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" style="background: #1d4ed8; color: #ffffff; border: none; padding: 11px 26px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; transition: 0.15s;">
                    🔑 Update Password
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('avatarPreview').src = reader.result;
    }
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
        document.getElementById('avatarFileName').textContent = '✓ Ready: ' + event.target.files[0].name;
    }
}
</script>
@endpush

@endsection