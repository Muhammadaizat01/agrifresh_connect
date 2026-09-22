@extends('layouts.app')

@section('content')
<div class="auth-wrap" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 1.25rem;">
    <div class="auth-card" style="width: 100%; max-width: 520px; background: #ffffff; border-radius: 32px; padding: 36px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-xl);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 18px; overflow: hidden; border: 2px solid #10b981;">
                <img src="{{ asset('assets/img/agrifresh_logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Create Your Account</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">Join AgriFresh Connect to buy fresh produce or register as a Kedah smallholder supplier.</p>
        </div>

        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 14px; border: 1px solid #fca5a5; font-size: 12px; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirect ?? '' }}">

            <!-- Role Selector Tabs -->
            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 8px;">I want to register as:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label style="border: 2px solid #10b981; background: #ecfdf5; border-radius: 14px; padding: 12px; text-align: center; cursor: pointer; display: block;">
                        <input type="radio" name="role_type" value="buyer" checked onchange="toggleFarmerFields(false)" style="margin-right: 6px;">
                        <strong>Buyer / Consumer</strong>
                    </label>
                    <label style="border: 2px solid #d1d5db; background: #f9fafb; border-radius: 14px; padding: 12px; text-align: center; cursor: pointer; display: block;" id="farmerRadioLabel">
                        <input type="radio" name="role_type" value="farmer" onchange="toggleFarmerFields(true)" style="margin-right: 6px;">
                        <strong>Kedah Smallholder</strong>
                    </label>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Muhammad Aizat / Pak Cik Azman" class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                <input type="email" name="email" required placeholder="name@example.com" class="form-input" style="width: 100%;">
            </div>

            div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Password</label>
                <input type="password" name="password" required placeholder="Create a secure password" class="form-input" style="width: 100%;">
                <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #6b7280; display: flex; align-items: center;">
                <svg class="icon-sm password-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Phone Number</label>
                    <input type="tel" name="phone" placeholder="+60 19-334 8812" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Delivery / Farm District</label>
                    <select name="district" class="form-input" style="width: 100%;">
                        <option value="Lunas, Kulim">Lunas, Kulim</option>
                        <option value="Kulim Central">Kulim Central</option>
                        <option value="Baling">Baling</option>
                        <option value="Sungai Petani">Sungai Petani</option>
                        <option value="Alor Setar">Alor Setar</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Address</label>
                <input type="text" name="address" placeholder="e.g. No. 12, Jalan Lunas Makmur 3" class="form-input" style="width: 100%;">
            </div>

            <!-- Farmer-Specific Additional Fields -->
            <div id="farmerFields" style="display: none; border-top: 1px dashed #d1d5db; padding-top: 16px;">
                <div style="margin-bottom: 12px;">
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Name / Ladang</label>
                    <input type="text" name="farm_name" placeholder="e.g. Ladang Hijau Makmur Lunas" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farming Certification</label>
                    <select name="cert" class="form-input" style="width: 100%;">
                        <option value="MyGAP Certified">MyGAP Certified</option>
                        <option value="Organic Certified">Organic Certified</option>
                        <option value="In Conversion to MyGAP">In Conversion to MyGAP</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="padding: 14px; font-size: 14px; margin-top: 8px; justify-content: center; width: 100%;">
                <span>Complete Registration</span> &rarr;
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: #6b7280;">
            Already have an account? 
            <a href="{{ route('login') }}" style="color: #059669; font-weight: 700; text-decoration: underline;">Sign In Here</a>
        </div>
    </div>
</div>

<script>
function toggleFarmerFields(isFarmer) {
    const fFields = document.getElementById('farmerFields');
    const fLabel = document.getElementById('farmerRadioLabel');
    if (isFarmer) {
        fFields.style.display = 'block';
        fLabel.style.borderColor = '#10b981';
        fLabel.style.background = '#ecfdf5';
    } else {
        fFields.style.display = 'none';
        fLabel.style.borderColor = '#d1d5db';
        fLabel.style.background = '#f9fafb';
    }
}
</script>
@endsection
