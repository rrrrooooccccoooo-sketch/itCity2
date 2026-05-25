<?php

use App\Http\Controllers\Central\TenantAdminController;
use App\Http\Controllers\Central\TenantBillingController;
use App\Http\Controllers\HomeController;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/central', 'central.landing')->name('central.landing');

Auth::routes();

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/admin/tenants', [TenantAdminController::class, 'index'])->name('admin.tenants.index');
    Route::post('/admin/tenants', [TenantAdminController::class, 'store'])->name('admin.tenants.store');
    Route::post('/admin/tenants/{tenant}/checkout', [TenantBillingController::class, 'checkout'])->name('admin.tenants.billing.checkout');
    Route::post('/admin/tenants/{tenant}/cancel', [TenantBillingController::class, 'cancel'])->name('admin.tenants.billing.cancel');
    Route::post('/admin/tenants/{tenant}/resume', [TenantBillingController::class, 'resume'])->name('admin.tenants.billing.resume');
    Route::get('/admin/tenants/{tenant}/billing/success', [TenantBillingController::class, 'success'])->name('admin.tenants.billing.success');
});
