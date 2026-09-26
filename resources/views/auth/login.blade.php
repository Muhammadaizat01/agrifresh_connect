@extends('layouts.app')

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-card-glass">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto 12px; border: 2px solid #10b981;">
                <img src="{{ asset('assets/img/agrifresh_logo.png') }}" alt="AgriFresh Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Sign In to AgriFresh</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">Direct marketplace for Famox Kedah supplier farmers</p>
        </div>

        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 14px; font-size: 12px; margin-bottom: 20px; border: 1px solid #fca5a5; text-align: center;">
                {{ session('error') }}
            </div>
        @endif

        <!-- 1-Click Demo Logins -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 20px; padding: 16px; margin-bottom: 24px;">
            <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; margin-bottom: 8px; text-align: center;">
                ⚡ 1-Click Log In
            </div>
            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <form method="POST" action="{{ route('login.submit') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <input type="hidden" name="demo_login" value="buyer">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>🛒 <strong>Muhammad Aizat</strong> (Direct Buyer)</span>
                        <span style="font-size: 11px; color: #16a34a; font-weight: 800;">Click to Login &rarr;</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('login.submit') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <input type="hidden" name="demo_login" value="farmer">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #fde68a; color: #92400e; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>👨‍🌾 <strong>Pak Cik Azman</strong> (Farmer Portal)</span>
                        <span style="font-size: 11px; color: #d97706; font-weight: 800;">Click to Login &rarr;</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('login.submit') }}" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <input type="hidden" name="demo_login" value="admin">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #bfdbfe; color: #1e40af; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>🏢 <strong>Famox Lunas Admin</strong> (Hub Oversight)</span>
                        <span style="font-size: 11px; color: #2563eb; font-weight: 800;">Click to Login &rarr;</span>
                    </button>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirect }}">

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                <input type="email" name="email" required placeholder="e.g. aizat@aimst.edu.my" class="form-input" style="width: 100%;">
            </div>

            <div>
    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Password</label>
    <div style="position: relative;">
        <input type="password" id="loginPassword" name="password" required placeholder="••••••••" class="form-input" style="width: 100%; padding-right: 40px;">
        <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #6b7280; display: flex; align-items: center;">
    <svg class="icon-sm password-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
</button>
    </div>
</div>
            <div style="text-align: center; margin-top: 6px;">
                <button type="submit" class="btn-primary" style="padding: 12px 36px; font-size: 13px; width: 100%; max-width: 260px; margin: 0 auto; display: inline-flex; justify-content: center;">
                    Sign In
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280;">
            Don't have an account? <a href="{{ route('register') }}" style="color: #059669; font-weight: 700;">Register Account &rarr;</a>
        </div>
    </div>
</div>
@endsection
