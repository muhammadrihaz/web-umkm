<?php

use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/katalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/kontak', [CatalogController::class, 'contact'])->name('contact');

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::get('/lupa-password', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/payments/midtrans/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

Route::middleware('auth')->group(function (): void {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/alamat', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::put('/keranjang/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/bayar', [PaymentController::class, 'initialize'])->name('payments.initialize');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::get('/stok', [InventoryController::class, 'index'])->name('inventory.index');
    Route::put('/stok/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/konfirmasi-cod', [AdminOrderController::class, 'confirmCod'])->name('orders.confirm-cod');
    Route::post('/pesanan/{order}/terima-cod', [AdminOrderController::class, 'receiveCod'])->name('orders.receive-cod');
    Route::post('/pesanan/{order}/fulfillment', [AdminOrderController::class, 'fulfillment'])->name('orders.fulfillment');
    Route::get('/pelanggan', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/pelanggan/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::resource('delivery-zones', DeliveryZoneController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/pengaturan', [BusinessSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [BusinessSettingController::class, 'update'])->name('settings.update');
    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan.csv', [ReportController::class, 'csv'])->name('reports.csv');
});
