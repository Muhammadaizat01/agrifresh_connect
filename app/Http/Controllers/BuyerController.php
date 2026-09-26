<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuyerController extends Controller
{
    private function sessionUser(): ?array
    {
        return session('user');
    }

    public function dashboard(): View|RedirectResponse
    {
        $user = $this->sessionUser();
        if (!$user) {
            return Redirect::route('login');
        }

        $userId   = $user['id'];
        $userName = $user['name'];

        $profile = DB::table('users')->where('id', $userId)->first();

        $orders = DB::table('orders')
            ->where(function ($query) use ($userId, $userName) {
                $query->where('user_id', $userId)
                      ->orWhere('buyer_name', $userName);
            })
            ->orderByDesc('created_at')
            ->get();

        foreach ($orders as $order) {
            $items = DB::table('order_items as oi')
                ->leftJoin('products as p', function ($join) {
                    $join->on('p.name', '=', 'oi.product_name')
                         ->orOn('p.name_ms', '=', 'oi.product_name');
                })
                ->leftJoin('farmers as fm', 'p.farmer_id', '=', 'fm.id')
                ->leftJoin('users as u', 'fm.user_id', '=', 'u.id')
                ->where('oi.order_id', $order->id)
                ->select(
                    'oi.*',
                    'p.batch_id',
                    'p.farm_location',
                    'p.image_path',
                    'fm.farm_name',
                    'u.name as farmer_name'
                )
                ->get();
            $order->items = $items;
        }

        $totalSpent  = collect($orders)->sum('total_amount');
        $activeCount = collect($orders)->filter(fn($o) => !str_contains(strtolower($o->status), 'delivered'))->count();

        return view('buyer.dashboard', compact('user', 'profile', 'orders', 'totalSpent', 'activeCount'));
    }

    public function settings(): View|RedirectResponse
    {
        $user = $this->sessionUser();
        if (!$user) {
            return Redirect::route('login');
        }

        $userId  = $user['id'];
        $profile = DB::table('users')->where('id', $userId)->first();

        return view('buyer.setting', compact('user', 'profile'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->sessionUser();
        if (!$user) {
            return Redirect::route('login');
        }

        $userId = $user['id'];

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address']);

        $existing = DB::table('users')->where('id', $userId)->value('avatar');
        $data['avatar'] = $existing;

        if ($request->hasFile('avatar_file')) {
            $file     = $request->file('avatar_file');
            $ext      = $file->getClientOriginalExtension();
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $file->move(public_path('assets/img/uploads'), $filename);
            $data['avatar'] = 'assets/img/uploads/' . $filename;
        }

        DB::table('users')->where('id', $userId)->update($data);

        $sessionUser = array_merge($user, [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? $user['phone'] ?? '',
            'address' => $data['address'] ?? $user['address'] ?? '',
            'avatar'  => $data['avatar'],
        ]);
        session(['user' => $sessionUser]);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->sessionUser();
        if (!$user) {
            return Redirect::route('login');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        $hash = DB::table('users')->where('id', $user['id'])->value('password');

        if (!Hash::check($request->current_password, (string) $hash) && $request->current_password !== $hash) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        DB::table('users')->where('id', $user['id'])->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully!');
    }
}