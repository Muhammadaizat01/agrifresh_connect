<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\ActivityLog;

class AdminController extends Controller
{
    public function panel()
    {
        $farmers = Farmer::with('user')->get();
        $orders = Order::orderBy('id', 'desc')->get();
        $logs = ActivityLog::orderBy('id', 'desc')->take(10)->get();

        return view('admin.panel', compact('farmers', 'orders', 'logs'));
    }
}
