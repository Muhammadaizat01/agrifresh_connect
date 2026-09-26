<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ActivityLog;

class AdminController extends Controller
{
    public function panel()
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login', ['redirect' => 'admin.panel'])->withErrors(['msg' => '🔒 Access Locked: Please sign in to access the Administration Portal.']);
        }

        $farmers = Farmer::with('user')->get();
        $orders = Order::orderBy('id', 'desc')->get();
        $logs = ActivityLog::orderBy('id', 'desc')->take(10)->get();

        $chartLabels = [];
        $chartSalesData = [];
        $chartVolumeData = [];

        foreach ($farmers as $fm) {
            // Aggregate sales for this farmer
            $sales = DB::table('order_items as oi')
                ->join('products as p', function ($join) {
                    $join->on('p.name', '=', 'oi.product_name')
                         ->orOn('p.name_ms', '=', 'oi.product_name');
                })
                ->where('p.farmer_id', $fm->id)
                ->select(
                    DB::raw('COALESCE(SUM(oi.subtotal), 0) as total_sales'),
                    DB::raw('COALESCE(SUM(oi.quantity), 0) as total_qty_sold'),
                    DB::raw('COUNT(DISTINCT oi.order_id) as total_orders')
                )
                ->first();

            $fm->total_sales = (float)($sales->total_sales ?? 0);
            $fm->total_qty_sold = (float)($sales->total_qty_sold ?? 0);
            $fm->total_orders = (int)($sales->total_orders ?? 0);

            // Calculate last activity date
            $lastProduct = DB::table('products')->where('farmer_id', $fm->id)->max('created_at');
            $fm->last_activity = $lastProduct ?: $fm->created_at;
            $days = $fm->last_activity ? (int)now()->diffInDays(\Carbon\Carbon::parse($fm->last_activity)) : 65;
            $fm->days_inactive = $days;

            $farmerDisplayName = $fm->user->name ?? $fm->farm_name;
            $chartLabels[] = $farmerDisplayName;
            $chartSalesData[] = $fm->total_sales;
            $chartVolumeData[] = $fm->total_qty_sold;
        }

        // Sort by total sales desc for leaderboard ranking
        $farmersSortedBySales = $farmers->sortByDesc('total_sales')->values();

        return view('admin.panel', compact(
            'farmers', 
            'orders', 
            'logs', 
            'chartLabels', 
            'chartSalesData', 
            'chartVolumeData',
            'farmersSortedBySales'
        ));
    }

    public function deleteFarmer(Request $request, int $id)
    {
        try {
            $farmer = Farmer::with('user')->findOrFail($id);
            $name = $farmer->user->name ?? $farmer->farm_name;

            // Delete associated products
            Product::where('farmer_id', $id)->delete();

            // Delete farmer profile
            $farmer->delete();

            // Log activity
            try {
                ActivityLog::create([
                    'user_name' => 'Famox Hub Admin',
                    'action' => 'Farmer Removed',
                    'description' => "Removed supplier partner '{$name}' (ID: {$id}) from Kedah network.",
                    'ip_address' => $request->ip() ?? '127.0.0.1'
                ]);
            } catch (\Throwable $e) {
                // Ignore if activity log fails
            }

            return redirect()->route('admin.panel')->with('success', "Farmer '{$name}' has been successfully removed from registered suppliers.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.panel')->withErrors(['msg' => 'Could not remove farmer: ' . $e->getMessage()]);
        }
    }
}

