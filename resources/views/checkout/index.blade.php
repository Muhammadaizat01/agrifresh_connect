@extends('layouts.app')

@section('content')
<div class="container-custom" style="padding: 40px 1.25rem 80px; max-width: 720px;">
    @if(isset($orderSuccess))
        <div style="background: #ffffff; border-radius: 32px; padding: 40px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); text-align: center;">
            <div style="width: 64px; height: 64px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
                ✓
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Thank You for Supporting Kedah Farmers!</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 6px;">
                Order <strong class="font-mono text-emerald">{{ $orderSuccess['orderNum'] }}</strong> has been recorded.
            </p>

            <script>
                localStorage.removeItem('af_cart');
                localStorage.setItem('af_cart', '[]');
                if (typeof cart !== 'undefined') { cart = []; }
                if (typeof updateCartUI === 'function') { updateCartUI(); }
            </script>

            <div style="margin-top: 24px; display: flex; justify-content: center; gap: 12px;">
                <a href="{{ route('store.index') }}" class="btn-primary" style="padding: 12px 24px;">Return to Storefront</a>
                <a href="{{ route('qr.verify', ['batch' => $orderSuccess['batchCode']]) }}" target="_blank" class="btn-secondary" style="padding: 12px 24px;">
                    📱 View QR Farm Passport
                </a>
            </div>
        </div>

    @elseif(!$user)
        <div style="background: #ffffff; border-radius: 32px; padding: 40px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg); text-align: center;">
            <div style="width: 60px; height: 60px; background: #ecfdf5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                🔒
            </div>
            <span class="badge-pill">Account Required to Checkout</span>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827; margin-top: 8px;">Please Sign In or Register First</h1>
            <p style="font-size: 13px; color: #6b7280; max-width: 480px; margin: 8px auto 24px;">
                To generate your verified QR code invoice and assign cold-chain delivery in Kedah, please sign in.
            </p>

            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('login', ['redirect' => 'checkout']) }}" class="btn-primary" style="padding: 12px 28px;">
                    Sign In with Password
                </a>
                <a href="{{ route('register', ['redirect' => 'checkout']) }}" class="btn-secondary" style="padding: 12px 28px;">
                    Register New Account
                </a>
            </div>
        </div>

    @else
        <div style="background: #ffffff; border-radius: 32px; padding: 36px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-lg);">
            <h1 style="font-size: 24px; font-weight: 900; color: #111827; margin-bottom: 20px;">Delivery & Payment Details</h1>

            <form method="POST" action="{{ route('checkout.placeOrder') }}" onsubmit="prepareCheckoutSubmit(event)">
                @csrf
                <input type="hidden" name="cart_data" id="checkoutCartData">
                <input type="hidden" name="total_amount" id="checkoutTotalAmount" value="45.40">

                <div style="display: flex; flex-direction: column; gap: 16px; font-size: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name / Buyer Account</label>
                        <input type="text" name="name" value="{{ $user['name'] }}" required class="form-input" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Contact Phone</label>
                        <input type="text" name="phone" value="{{ $user['phone'] ?? '+60 19-334 8812' }}" required class="form-input" style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Delivery Address (Kedah)</label>
                        <textarea name="address" rows="2" required class="form-input" style="width: 100%;">{{ $user['address'] ?? 'No. 12, Jalan Lunas Makmur 3, 09600 Lunas, Kedah' }}</textarea>
                    </div>

                    <div style="text-align: center; margin-top: 10px;">
                        <button type="submit" class="btn-primary" style="padding: 14px 40px; font-size: 14px; width: 100%; max-width: 360px; margin: 0 auto; display: inline-flex; justify-content: center;">
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
        </script>
    @endif
</div>
@endsection
