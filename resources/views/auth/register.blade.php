@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-card-glass" style="max-width: 580px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto 12px; border: 2px solid #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.25);">
                <img src="{{ asset('assets/img/agrifresh_logo.png') }}" alt="AgriFresh Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Create Your Account</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">Join AgriFresh Connect to buy fresh produce or register as a Kedah smallholder supplier.</p>
        </div>

        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 14px; border: 1px solid #fca5a5; font-size: 12px; margin-bottom: 20px; text-align: center;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirect ?? '' }}">

            <!-- Role Selector Tabs -->
            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 6px;">Select Your Account Role</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label id="buyerRadioLabel" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 2px solid #10b981; background: #ecfdf5; border-radius: 12px; cursor: pointer; transition: all 0.2s ease;">
                        <input type="radio" name="role_type" value="buyer" checked onchange="toggleFarmerFields(false)">
                        <div>
                            <strong style="color: #065f46; display: block;">🛒 Buyer / Consumer</strong>
                            <div style="font-size: 10px; color: #6b7280;">Purchase Fresh Produce</div>
                        </div>
                    </label>

                    <label id="farmerRadioLabel" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 2px solid #d1d5db; background: #f9fafb; border-radius: 12px; cursor: pointer; transition: all 0.2s ease;">
                        <input type="radio" name="role_type" value="farmer" onchange="toggleFarmerFields(true)">
                        <div>
                            <strong style="color: #92400e; display: block;">👨‍🌾 Kedah Smallholder</strong>
                            <div style="font-size: 10px; color: #6b7280;">Sell Crops & Print QR</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Muhammad Aizat / Pak Cik Azman" class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="registerPassword" name="password" required placeholder="Create password" class="form-input" style="width: 100%; padding-right: 36px;">
                        <button type="button" onclick="togglePasswordVisibility('registerPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #6b7280; display: flex; align-items: center;">
                            <svg class="icon-sm password-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
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
            <div id="farmerFields" style="display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 14px;">
                <div style="font-weight: 800; color: #92400e; margin-bottom: 8px;">👨‍🌾 Farm Details (Famox Supplier Network)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Name / Ladang</label>
                        <input type="text" name="farm_name" placeholder="e.g. Ladang Hijau Makmur" class="form-input" style="width: 100%;">
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
            </div>

            <!-- Submit Button -->
            <div style="text-align: center; margin-top: 6px;">
                <button type="submit" class="btn-primary" style="padding: 13px 40px; font-size: 14px; width: 100%; max-width: 280px; margin: 0 auto; display: inline-flex; justify-content: center; align-items: center; gap: 8px;">
                    <span>Complete Registration</span> &rarr;
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280;">
            Already have an account? 
            <a href="{{ route('login') }}" style="color: #059669; font-weight: 700; text-decoration: underline;">Sign In Here</a>
        </div>
    </div>
</div>

<script>
function toggleFarmerFields(isFarmer) {
    const fFields = document.getElementById('farmerFields');
    const fLabel = document.getElementById('farmerRadioLabel');
    const bLabel = document.getElementById('buyerRadioLabel');
    if (isFarmer) {
        fFields.style.display = 'block';
        fLabel.style.borderColor = '#10b981';
        fLabel.style.background = '#ecfdf5';
        bLabel.style.borderColor = '#d1d5db';
        bLabel.style.background = '#f9fafb';
    } else {
        fFields.style.display = 'none';
        fLabel.style.borderColor = '#d1d5db';
        fLabel.style.background = '#f9fafb';
        bLabel.style.borderColor = '#10b981';
        bLabel.style.background = '#ecfdf5';
    }
}
</script>
@endsection
