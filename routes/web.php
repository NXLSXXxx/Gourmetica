<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showClientLogin'])->name('login');
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('auth/google', [App\Http\Controllers\SocialController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\SocialController::class, 'handleGoogleCallback']);

Route::redirect('/catalog', '/shop');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/category/{category:slug}', [ShopController::class, 'getByCategory'])->name('shop.category.ajax');
Route::get('/shop/product/{slug}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('shop.cart');
Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::get('/cart/remove/{key}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::post('/select-headquarter', function(\Illuminate\Http\Request $request) {
    $request->validate([
        'headquarter_id' => 'required|exists:headquarters,id'
    ]);
    session()->put('selected_headquarter_id', $request->headquarter_id);
    return response()->json(['success' => true]);
})->name('select-headquarter');
Route::get('/locations', [LocationController::class, 'index'])->name('shop.locations');
Route::get('/about', function () {
    return view('shop.about');
})->name('shop.about');

Route::get('/contact', function () {
    return view('shop.contact');
})->name('shop.contact');

Route::get('/catering', [\App\Http\Controllers\Shop\CateringController::class, 'index'])->name('shop.catering');
Route::post('/catering', [\App\Http\Controllers\Shop\CateringController::class, 'store'])->name('shop.catering.store');

Route::middleware(['auth:web'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/favorites/{product}/toggle', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
});

Route::middleware(['admin'])->prefix('intranet')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('intranet.dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients');
    Route::resource('/products', ProductController::class)->names('admin.products');
    Route::resource('/categories', CategoryController::class)->names('admin.categories');
    Route::resource('/headquarters', \App\Http\Controllers\Admin\HeadquarterController::class)->names('admin.headquarters');
    Route::resource('/banners', \App\Http\Controllers\Admin\BannerController::class)->names('admin.banners');
    Route::resource('/delivery-zones', \App\Http\Controllers\Admin\DeliveryZoneController::class)->names('admin.delivery_zones');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update_status');
    Route::get('/orders/{id}/ticket', [AdminOrderController::class, 'printTicket'])->name('admin.orders.ticket');
    Route::get('/sales', [\App\Http\Controllers\Admin\SaleController::class, 'index'])->name('admin.sales.index');
    Route::post('/sales/direct', [\App\Http\Controllers\Admin\SaleController::class, 'storeDirect'])->name('admin.sales.store_direct');
    Route::get('/pos', [\App\Http\Controllers\Admin\SaleController::class, 'pos'])->name('admin.pos.index');
    Route::post('/pos/store', [\App\Http\Controllers\Admin\SaleController::class, 'posStore'])->name('admin.pos.store');
    Route::post('/pos/pre-order', [\App\Http\Controllers\Admin\SaleController::class, 'posPreOrder'])->name('admin.pos.pre_order');
    Route::post('/pos/cancel-order', [\App\Http\Controllers\Admin\SaleController::class, 'posCancelOrder'])->name('admin.pos.cancel_order');
    Route::post('/sales/{id}/declare', [\App\Http\Controllers\Admin\SaleController::class, 'declareToSunat'])->name('admin.sales.declare');

    Route::get('/catering', [\App\Http\Controllers\Admin\CateringController::class, 'index'])->name('admin.catering.index');
    Route::get('/catering/{cateringRequest}', [\App\Http\Controllers\Admin\CateringController::class, 'show'])->name('admin.catering.show');
    Route::patch('/catering/{cateringRequest}/status', [\App\Http\Controllers\Admin\CateringController::class, 'updateStatus'])->name('admin.catering.update_status');

    Route::resource('/purchases', \App\Http\Controllers\Admin\PurchaseController::class)->names('admin.purchases');
    
    Route::resource('/users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');
    
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::patch('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('admin.logs.index');
    Route::get('/backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('admin.backups.index');
    Route::post('/backups', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('admin.backups.create');
    Route::get('/audits', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('admin.audits.index');
});
