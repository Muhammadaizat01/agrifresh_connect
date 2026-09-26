<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\User;

class FarmerController extends Controller
{
    public function dashboard()
    {
        $user = session('user');
        $farmer = null;
        if ($user) {
            $farmer = Farmer::with('user')->where('user_id', $user['id'])->first();
        }
        if (!$farmer) {
            $farmer = Farmer::with('user')->first();
        }

        $farmerProducts = Product::where('farmer_id', $farmer->id ?? 1)->orderBy('id', 'desc')->get();
        if ($farmerProducts->isEmpty()) {
            $farmerProducts = Product::orderBy('id', 'desc')->take(6)->get();
        }

        $recentOrders = Order::with('items')->orderBy('id', 'desc')->take(10)->get();
        $categories = Category::all();

        // Notifications for farmer (safely wrapped)
        $notifications = collect();
        $unreadCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                $notifications = \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('notifiable_id', $farmer->user_id ?? 2)
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();

                $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('notifiable_id', $farmer->user_id ?? 2)
                    ->whereNull('read_at')
                    ->count();
            }
        } catch (\Throwable $e) {
            // Safe fallback if notifications table is missing
        }

        // Default unread count to recent orders count if notifications empty
        if ($unreadCount === 0 && $notifications->isEmpty()) {
            $unreadCount = $recentOrders->count();
        }

        return view('farmer.dashboard', compact('farmer', 'farmerProducts', 'recentOrders', 'categories', 'notifications', 'unreadCount'));
    }

    public function storeProduce(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0.10'
        ]);

        $user = session('user');
        $farmer = $user ? Farmer::where('user_id', $user['id'])->first() : null;
        $farmerId = $farmer ? $farmer->id : 1;

        $batchId = 'AF-LUN-' . date('Y') . '-' . rand(100, 999);
        $imagePath = 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800';

        if ($request->hasFile('product_photo')) {
            $uploadDir = public_path('assets/img/uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $file = $request->file('product_photo');
            $filename = 'crop_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = 'assets/img/uploads/' . $filename;
            @copy($uploadDir . '/' . $filename, base_path('assets/img/uploads/' . $filename));
        } elseif (!empty($request->image_url)) {
            $imagePath = trim($request->image_url);
        }

        Product::create([
            'batch_id' => $batchId,
            'farmer_id' => $farmerId,
            'category_id' => $request->input('category_id', 2),
            'name' => $request->name,
            'name_ms' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'description_ms' => $request->description,
            'harvest_date' => $request->input('harvest_date', date('Y-m-d') . ' 05:30 AM'),
            'quantity_available' => $request->input('stock', 100),
            'unit' => $request->input('unit', 'kg'),
            'price_per_unit' => $request->price,
            'middleman_price' => $request->price * 1.5,
            'farm_location' => $farmer ? ($farmer->farm_location_details ?: 'Lunas, Kedah') : 'Lunas, Kedah',
            'grade' => $request->input('grade', 'Grade A Premium'),
            'pesticide_status' => 'MyGAP Lab Tested (Safe 0.00 ppm)',
            'image_path' => $imagePath,
            'tag' => 'FRESH HARVEST',
            'approval_status' => 'approved'
        ]);

        return redirect()->route('farmer.dashboard')->with('success', "Produce '{$request->name}' published successfully with Batch {$batchId}!");
    }

    public function updateProduce(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0.10'
        ]);

        try {
            $product = Product::findOrFail($id);

            if ($request->hasFile('product_photo')) {
                $uploadDir = public_path('assets/img/uploads');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $file = $request->file('product_photo');
                $filename = 'crop_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $product->image_path = 'assets/img/uploads/' . $filename;
                @copy($uploadDir . '/' . $filename, base_path('assets/img/uploads/' . $filename));
            } elseif (!empty($request->image_url)) {
                $product->image_path = trim($request->image_url);
            }

            $product->name = $request->name;
            $product->name_ms = $request->name;
            $product->price_per_unit = $request->price;
            $product->middleman_price = $request->price * 1.5;
            $product->unit = $request->input('unit', $product->unit);
            $product->quantity_available = $request->input('stock', $product->quantity_available);
            $product->grade = $request->input('grade', $product->grade);
            $product->category_id = $request->input('category_id', $product->category_id);
            $product->description = $request->input('description', $product->description);
            $product->description_ms = $request->input('description', $product->description_ms);
            $product->save();

            return redirect()->route('farmer.dashboard')->with('success', "Produce '{$product->name}' and photo updated successfully!");
        } catch (\Throwable $e) {
            return redirect()->route('farmer.dashboard')->withErrors(['msg' => 'Could not update produce: ' . $e->getMessage()]);
        }
    }

    public function destroyProduce(Request $request, int $id)
    {
        try {
            $product = Product::findOrFail($id);
            $name = $product->name;
            $product->delete();

            return redirect()->route('farmer.dashboard')->with('success', "Produce '{$name}' has been successfully removed from your catalog.");
        } catch (\Throwable $e) {
            return redirect()->route('farmer.dashboard')->withErrors(['msg' => 'Could not remove produce: ' . $e->getMessage()]);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $userSession = session('user');
            $farmer = null;
            $user = null;

            if ($userSession && !empty($userSession['id'])) {
                $user = User::find($userSession['id']);
                $farmer = Farmer::where('user_id', $userSession['id'])->first();
            }
            if (!$farmer) {
                $farmer = Farmer::first();
            }
            if (!$user && $farmer && $farmer->user_id) {
                $user = User::find($farmer->user_id);
            }

            if ($request->hasFile('avatar_photo')) {
                $uploadDir = public_path('assets/img/uploads');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $file = $request->file('avatar_photo');
                $filename = 'avatar_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                if ($farmer) {
                    $farmer->avatar = 'assets/img/uploads/' . $filename;
                }
                @copy($uploadDir . '/' . $filename, base_path('assets/img/uploads/' . $filename));
            } elseif (!empty($request->avatar_url) && $farmer) {
                $farmer->avatar = trim($request->avatar_url);
            }

            if (!empty($request->name) && $user) {
                $user->name = $request->name;
            }
            if (!empty($request->phone) && $user) {
                $user->phone = $request->phone;
            }
            if ($user) {
                $user->save();
            }

            if ($farmer) {
                if (!empty($request->farm_name)) {
                    $farmer->farm_name = $request->farm_name;
                }
                if (!empty($request->location)) {
                    $farmer->farm_location_details = $request->location;
                }
                if (!empty($request->quote)) {
                    $farmer->quote = $request->quote;
                }
                if (!empty($request->cert_number)) {
                    $farmer->cert_number = $request->cert_number;
                }
                $farmer->save();
            }

            if ($user) {
                $userSession = session('user') ?? [];
                $userSession['name'] = $user->name;
                $userSession['phone'] = $user->phone;
                if ($farmer && $farmer->avatar) {
                    $userSession['avatar'] = $farmer->avatar;
                }
                session(['user' => $userSession]);
            }

            return redirect()->route('farmer.dashboard')->with('success', 'Profile information and avatar photo updated successfully!');
        } catch (\Throwable $e) {
            return redirect()->route('farmer.dashboard')->withErrors(['msg' => 'Could not update profile: ' . $e->getMessage()]);
        }
    }
}