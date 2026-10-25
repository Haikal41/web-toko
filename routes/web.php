<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AttendanceAdminController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\WhatsappAdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::patch('/keranjang/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{key}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pembayaran/{orderNumber}', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/pembayaran/{orderNumber}/demo', [CheckoutController::class, 'demoPay'])->name('checkout.demo');
    Route::get('/pesanan/{orderNumber}/selesai', [CheckoutController::class, 'finish'])->name('checkout.finish');

    Route::get('/akun', [AccountController::class, 'index'])->name('account.index');
    Route::put('/akun', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/akun/alamat', [AccountController::class, 'storeAddress'])->name('account.address.store');
    Route::delete('/akun/alamat/{address}', [AccountController::class, 'destroyAddress'])->name('account.address.destroy');
    Route::post('/akun/alamat/{address}/utama', [AccountController::class, 'setDefaultAddress'])->name('account.address.default');
    Route::get('/akun/pesanan/{orderNumber}', [AccountController::class, 'showOrder'])->name('account.order.show');

    Route::get('/absensi', [AttendanceController::class, 'index'])->middleware('role:karyawan,admin')->name('attendance.index');
    Route::post('/absensi/scan', [AttendanceController::class, 'scan'])->middleware('role:karyawan,admin')->name('attendance.scan');
    Route::get('/absensi/riwayat', [AttendanceController::class, 'history'])->middleware('role:karyawan,admin')->name('attendance.history');
});

Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhooks.midtrans');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/produk', [ProductAdminController::class, 'index'])->name('products.index');
    Route::get('/produk/create', [ProductAdminController::class, 'create'])->name('products.create');
    Route::post('/produk', [ProductAdminController::class, 'store'])->name('products.store');
    Route::get('/produk/{product}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
    Route::put('/produk/{product}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('/produk/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
    Route::delete('/produk/{product}/foto/{image}', [ProductAdminController::class, 'destroyImage'])->name('products.image.destroy');
    Route::patch('/produk/{product}/foto/{image}/utama', [ProductAdminController::class, 'setPrimaryImage'])->name('products.image.primary');
    Route::delete('/produk/{product}/varian/{variant}', [ProductAdminController::class, 'destroyVariant'])->name('products.variant.destroy');

    Route::get('/kategori', [CategoryAdminController::class, 'index'])->name('categories.index');
    Route::post('/kategori', [CategoryAdminController::class, 'store'])->name('categories.store');
    Route::put('/kategori/{category}', [CategoryAdminController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [CategoryAdminController::class, 'destroy'])->name('categories.destroy');

    Route::get('/pesanan', [OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
    Route::patch('/pesanan/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/karyawan', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/karyawan', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/karyawan/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/karyawan/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/absensi', [AttendanceAdminController::class, 'index'])->name('attendance.index');
    Route::post('/absensi/qr', [AttendanceAdminController::class, 'generate'])->name('attendance.generate');
    Route::put('/absensi/pengaturan', [AttendanceAdminController::class, 'updateSettings'])->name('attendance.settings');
    Route::get('/absensi/rekap', [AttendanceAdminController::class, 'recap'])->name('attendance.recap');
    Route::get('/absensi/rekap/export', [AttendanceAdminController::class, 'export'])->name('attendance.export');

    Route::get('/kupon', [CouponAdminController::class, 'index'])->name('coupons.index');
    Route::post('/kupon', [CouponAdminController::class, 'store'])->name('coupons.store');
    Route::put('/kupon/{coupon}', [CouponAdminController::class, 'update'])->name('coupons.update');
    Route::delete('/kupon/{coupon}', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');

    Route::get('/whatsapp', [WhatsappAdminController::class, 'index'])->name('whatsapp.index');
    Route::post('/whatsapp/test', [WhatsappAdminController::class, 'test'])->name('whatsapp.test');

    Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
});
