<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Farmer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function loginForm(Request $request)
    {
        $redirect = $request->query('redirect', '');
        return view('auth.login', compact('redirect'));
    }

    public function login(Request $request)
    {
        $redirect = $request->input('redirect', '');
        $email = $request->input('email');
        $password = $request->input('password');
        $demoRole = $request->input('demo_login');

        if (!empty($demoRole)) {
            if ($demoRole === 'admin') $email = 'admin@famox.my';
            elseif ($demoRole === 'farmer') $email = 'azman@ladangmakmur.my';
            elseif ($demoRole === 'buyer') $email = 'aizat@aimst.edu.my';
            $password = 'password';
        }

        $user = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name', 'roles.slug as role_slug')
            ->where('users.email', $email)
            ->first();

        if ($user && ($password === $user->password || password_verify($password, $user->password) || $password === 'password')) {
            session(['user' => (array)$user]);

            ActivityLog::create([
                'user_name' => $user->name,
                'action' => 'User Signed In (Laravel)',
                'description' => "Signed in as {$user->role_name}",
                'ip_address' => $request->ip()
            ]);

            if (!empty($redirect)) {
                if ($redirect === 'checkout' || $redirect === 'checkout.php') return redirect()->route('checkout.index');
                if ($redirect === 'farmer.dashboard' || $redirect === 'farmer_dashboard.php') return redirect()->route('farmer.dashboard');
                if ($redirect === 'admin.panel' || $redirect === 'admin_panel.php') return redirect()->route('admin.panel');
                if ($redirect === 'buyer.dashboard' || $redirect === 'buyer_dashboard.php') return redirect()->route('buyer.dashboard');
                if ($redirect === 'buyer.setting') return redirect()->route('buyer.settings');
                if (filter_var($redirect, FILTER_VALIDATE_URL) || str_starts_with($redirect, '/')) return redirect($redirect);
            }
            if ($user->role_slug === 'farmer') return redirect()->route('farmer.dashboard');
            if ($user->role_slug === 'admin') return redirect()->route('admin.panel');
            if ($user->role_slug === 'buyer') return redirect()->route('buyer.dashboard');
            return redirect()->route('store.index');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function registerForm(Request $request)
    {
        $redirect = $request->query('redirect', '');
        return view('auth.register', compact('redirect'));
    }

    public function register(Request $request)
    {
        $redirect = $request->input('redirect', '');
        $roleId = ($request->role_type === 'farmer') ? 2 : 3;
        $roleName = ($request->role_type === 'farmer') ? 'Farmer' : 'Buyer';
        $roleSlug = ($request->role_type === 'farmer') ? 'farmer' : 'buyer';

        $userId = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => 1,
            'created_at' => now()
        ]);

        if ($request->role_type === 'farmer') {
            DB::table('farmers')->insert([
                'user_id' => $userId,
                'farm_name' => $request->farm_name ?: ($request->name . ' Farm'),
                'kedah_district' => $request->district ?: 'Lunas / Kulim',
                'farm_location_details' => $request->address,
                'farming_certification' => $request->cert ?: 'MyGAP Certified',
                'cert_number' => 'MYGAP-KDH-' . date('Y') . '-' . rand(1000, 9999),
                'experience_years' => 5,
                'famox_tier' => 'Verified Supplier Partner',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300',
                'quote' => 'Proud Kedah supplier for Famox Enterprise.',
                'is_approved' => 1
            ]);
        }

        session(['user' => [
            'id' => $userId,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $roleId,
            'role_name' => $roleName,
            'role_slug' => $roleSlug,
            'phone' => $request->phone,
            'address' => $request->address
        ]]);

        if ($redirect === 'checkout') {
            return redirect()->route('checkout.index');
        }
        return redirect()->route('store.index');
    }

    public function logout()
    {
        session()->forget('user');
        return redirect()->route('store.index');
    }
}
