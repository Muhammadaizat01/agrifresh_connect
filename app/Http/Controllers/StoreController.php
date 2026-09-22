<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Farmer;
use App\Models\PriceIndex;

class StoreController extends Controller
{
    /**
     * Display the Agrifresh Connect marketplace.
     */
    public function index(Request $request)
    {
        $categories = Category::all();

        $selectedCat = $request->query('cat', 'all');
        $search = trim($request->query('q', ''));

        $query = Product::with(['farmer.user', 'category'])
            ->where('approval_status', '!=', 'rejected');

        // Filter by category
        if ($selectedCat !== 'all') {
            $query->whereHas('category', function ($q) use ($selectedCat) {
                $q->where('slug', $selectedCat);
            });
        }

        // Search products
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('name_ms', 'LIKE', "%{$search}%")
                    ->orWhere('batch_id', 'LIKE', "%{$search}%")
                    ->orWhere('farm_location', 'LIKE', "%{$search}%");
            });
        }

        $products = $query
            ->orderBy('id', 'desc')
            ->get();

        // Spotlight products
        $spotlights = Product::with(['farmer.user'])
            ->where('is_spotlight', 1)
            ->get();

        // If there are no spotlight products,
        // use the latest 4 products.
        if ($spotlights->isEmpty()) {
            $spotlights = $products->take(4);
        }

        $priceIndex = PriceIndex::all();

        $farmers = Farmer::with('user')->get();

        return view(
            'store.index',
            compact(
                'categories',
                'products',
                'spotlights',
                'priceIndex',
                'farmers',
                'selectedCat',
                'search'
            )
        );
    }


    /**
     * Display QR traceability information.
     *
     * Example:
     * /qr-verify/AF-LUN-2026-265
     *
     * Also supports:
     * /qr-verify?batch=AF-LUN-2026-265
     */
    public function traceQr(Request $request, $batch = null)
    {
        // Get batch from URL parameter or query string.
        $batch = $batch ?: $request->query('batch');

        // Do not use a fake/default batch.
        if (empty($batch)) {
            abort(404, 'Batch ID is required.');
        }

        // Find the EXACT product belonging to this batch.
        // Also load the farmer and farmer's user account.
        $product = Product::with([
            'farmer.user',
            'category'
        ])
            ->where('batch_id', $batch)
            ->firstOrFail();

        return view('qr.verify', compact('product'));
    }


    /**
     * Return QR product information as JSON.
     */
    public function getProductQr(Request $request)
    {
        $batch = trim($request->query('batch', ''));

        if (empty($batch)) {
            return response()->json([
                'success' => false,
                'message' => 'Batch ID required'
            ], 400);
        }

        $prod = Product::with([
            'farmer.user',
            'category'
        ])
            ->where('batch_id', $batch)
            ->first();

        if (!$prod) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found in MySQL database'
            ], 404);
        }

        /*
         * Farmer information
         */
        $farmerName = $prod->farmer?->user?->name ?? 'Unknown Farmer';
        $farmName = $prod->farmer?->farm_name ?? 'Farm information unavailable';

        /*
         * Farmer profile picture
         *
         * IMPORTANT:
         * We return the avatar belonging to the farmer
         * connected to THIS product.
         */
        $farmerAvatar = $prod->farmer?->avatar;

        if (!empty($farmerAvatar)) {
            // If database already contains a full URL
            if (
                str_starts_with($farmerAvatar, 'http://') ||
                str_starts_with($farmerAvatar, 'https://')
            ) {
                $farmerAvatarUrl = $farmerAvatar;
            } else {
                // Local Laravel/public image
                $farmerAvatarUrl = asset(
                    ltrim($farmerAvatar, '/')
                );
            }
        } else {
            // Generic fallback picture
            $farmerAvatarUrl = asset(
                'assets/img/default-farmer.png'
            );
        }

        /*
         * Product image
         */
        $productImage = $prod->image_path;

        if (!empty($productImage)) {
            if (
                str_starts_with($productImage, 'http://') ||
                str_starts_with($productImage, 'https://')
            ) {
                $productImageUrl = $productImage;
            } else {
                $productImageUrl = asset(
                    ltrim($productImage, '/')
                );
            }
        } else {
            $productImageUrl = null;
        }

        return response()->json([
            'success' => true,

            // Product
            'batchId' => $prod->batch_id,
            'name' => $prod->name,
            'nameMs' => $prod->name_ms,
            'price' => $prod->price_per_unit,
            'unit' => $prod->unit,

            // Farmer
            'farmerName' => $farmerName,
            'farmerAvatar' => $farmerAvatarUrl,
            'farmName' => $farmName,

            // Traceability
            'location' => $prod->farm_location ?? 'Location unavailable',
            'harvestDate' => $prod->harvest_date,
            'grade' => $prod->grade,
            'pesticide' => $prod->pesticide_status,
            'storage' => $prod->storage_temp,

            // Certification
            'cert' => $prod->farmer?->cert_number ?? 'Not available',

            // Product image
            'image' => $productImageUrl
        ]);
    }
}