<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ActivityLog;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = session('user');
        return view('checkout.index', compact('user'));
    }

    public function placeOrder(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login', ['redirect' => 'checkout']);
        }

        $orderNum = 'ORD-KDH-' . rand(1000, 9999);
        $cartItems = json_decode($request->input('cart_data', '[]'), true) ?: [];
        $batchCode = !empty($cartItems) ? $cartItems[0]['batchId'] : 'AF-LUN-2026-089';

        $order = Order::create([
            'user_id' => $user['id'] ?? null,
            'order_number' => $orderNum,
            'buyer_name' => $request->input('name', $user['name']),
            'buyer_role' => $user['role_name'] ?? 'Direct Consumer',
            'phone' => $request->input('phone', $user['phone'] ?? '+60 19-334 8812'),
            'total_amount' => $request->input('total_amount', 45.40),
            'status' => 'Order Confirmed & QR Tagged',
            'payment_method' => $request->input('payment_method', 'FPX Online Banking (Maybank)'),
            'shipping_address' => $request->input('address', $user['address'] ?? 'No. 12, Jalan Lunas Makmur 3, 09600 Lunas, Kedah'),
            'batch_code' => $batchCode,
            'driver' => 'Famox Logistics Lunas Hub (Van KDH 4410)'
        ]);

        foreach ($cartItems as $it) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $it['name'],
                'quantity' => $it['qty'],
                'unit' => $it['unit'],
                'price' => $it['price'],
                'subtotal' => $it['price'] * $it['qty']
            ]);
        }

        ActivityLog::create([
            'user_name' => $user['name'],
            'action' => 'Order Placed (Laravel)',
            'description' => "Placed order $orderNum (RM " . number_format($request->input('total_amount', 45.40), 2) . ")",
            'ip_address' => $request->ip()
        ]);

        return view('checkout.index', [
            'user' => $user,
            'orderSuccess' => [
                'orderNum' => $orderNum,
                'name' => $request->input('name', $user['name']),
                'phone' => $request->input('phone', $user['phone'] ?? '+60 19-334 8812'),
                'total' => $request->input('total_amount', 45.40),
                'payment' => $request->input('payment_method'),
                'batchCode' => $batchCode,
                'driver' => 'Famox Logistics Lunas Hub (Van KDH 4410)'
            ]
        ]);
    }
}
