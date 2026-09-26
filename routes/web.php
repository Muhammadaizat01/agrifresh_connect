<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\StoreController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\LanguageController;

Route::get('/lang/{lang}', [LanguageController::class, 'switchLanguage'])
    ->name('lang.switch');

Route::get('/', [StoreController::class, 'index'])
    ->name('store.index');

Route::get('/qr-verify/{batch?}', [StoreController::class, 'traceQr'])
    ->name('qr.verify');

Route::get('/api/get-product-qr', [StoreController::class, 'getProductQr'])
    ->name('api.productQr');

Route::get('/qr_verify.php', function (Request $request) {

    $batch = $request->query('batch');

    if (!$batch) {
        return redirect()->route('store.index');
    }

    return redirect()->route('qr.verify', [
        'batch' => $batch
    ]);

});

Route::get('/api/get_product_qr.php', [StoreController::class, 'getProductQr']);

Route::get('/buyer-dashboard', [BuyerController::class, 'dashboard'])
    ->name('buyer.dashboard');

Route::get('/buyer-settings', [BuyerController::class, 'settings'])
    ->name('buyer.settings');

Route::post('/buyer-settings/profile', [BuyerController::class, 'updateProfile'])
    ->name('buyer.updateProfile');

Route::post('/buyer-settings/password', [BuyerController::class, 'updatePassword'])
    ->name('buyer.updatePassword');

Route::get('/buyer_dashboard.php', function () {
    return redirect()->route('buyer.dashboard');
});

Route::get('/dashboard.blade', function () {
    return redirect()->route('buyer.dashboard');
});

Route::get('/dashboard.blade.php', function () {
    return redirect()->route('buyer.dashboard');
});


Route::match(['get', 'post'], '/settings.php', function (Request $request) {

    if ($request->isMethod('post')) {

        $action = $request->input('action');

        if ($action === 'update_profile') {

            return app(BuyerController::class)
                ->updateProfile($request);

        }

        if ($action === 'update_password') {

            return app(BuyerController::class)
                ->updatePassword($request);

        }
    }

    return redirect()->route('buyer.settings');

});

Route::get('/farmer-dashboard', [FarmerController::class, 'dashboard'])
    ->name('farmer.dashboard');

Route::post(
    '/farmer-dashboard/produce',
    [FarmerController::class, 'storeProduce']
)->name('farmer.storeProduce');


Route::post(
    '/farmer-dashboard/produce/{id}',
    [FarmerController::class, 'updateProduce']
)->name('farmer.updateProduce');


Route::post(
    '/farmer-dashboard/produce/{id}/delete',
    [FarmerController::class, 'destroyProduce']
)->name('farmer.destroyProduce');

Route::post(
    '/farmer-dashboard/produce/{id}/restock',
    [FarmerController::class, 'restockProduce']
)->name('farmer.restockProduce');

Route::post(
    '/farmer-dashboard/order/{id}/decline',
    [FarmerController::class, 'declineOrder']
)->name('farmer.declineOrder');

Route::post(
    '/farmer-dashboard/profile',
    [FarmerController::class, 'updateProfile']
)->name('farmer.updateProfile');

Route::match(
    ['get', 'post'],
    '/farmer_dashboard.php',
    function (Request $request) {

        if ($request->isMethod('post')) {

            $action = $request->input('action');

            if ($action === 'quick_restock') {
                $productId = (int) $request->input('product_id');
                return app(FarmerController::class)->restockProduce($request, $productId);
            }

            if ($action === 'decline_order') {
                $orderId = (int) $request->input('order_id');
                return app(FarmerController::class)->declineOrder($request, $orderId);
            }

            if ($action === 'update_profile') {

                return app(FarmerController::class)
                    ->updateProfile($request);

            }

            if ($action === 'update_produce') {

                $productId = (int) $request->input('product_id');

                return app(FarmerController::class)
                    ->updateProduce(
                        $request,
                        $productId
                    );

            }

            if ($action === 'add_produce') {

                return app(FarmerController::class)
                    ->storeProduce($request);

            }

        }


        return redirect()->route('farmer.dashboard');

    }
);

Route::get('/admin-panel', [AdminController::class, 'panel'])
    ->name('admin.panel');

Route::get('/admin_panel.php', function () {
    return redirect()->route('admin.panel');
});

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->name('checkout.index');

Route::post('/checkout', [CheckoutController::class, 'placeOrder'])
    ->name('checkout.placeOrder');

Route::get('/checkout.php', function () {
    return redirect()->route('checkout.index');
});

Route::get('/login', [AuthController::class, 'loginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');


Route::get('/register', [AuthController::class, 'registerForm'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.submit');


Route::get('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get('/login.php', function () {
    return redirect()->route('login');
});

Route::get('/register.php', function () {
    return redirect()->route('register');
});

Route::get('/logout.php', function () {
    return redirect()->route('logout');
});

Route::get('/index.php', function () {
    return redirect()->route('store.index');
});