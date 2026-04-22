<?php

use App\Http\Controllers\OsemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResellerController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/new-order', function () {
    return Inertia::render('NewOrder');
})->middleware(['auth', 'verified'])->name('new-order');

Route::get('/topup-balance', function () {
    return Inertia::render('TopupBalance');
})->middleware(['auth', 'verified'])->name('topup-balance');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('osem')->name('osem.')->group(function () {
        Route::get('/orders', [OsemController::class, 'orders'])->name('orders');
        Route::post('/services', [OsemController::class, 'services'])->name('services');
        Route::post('/add', [OsemController::class, 'add'])->name('add');
        Route::post('/status', [OsemController::class, 'status'])->name('status');
        Route::post('/statuses', [OsemController::class, 'statuses'])->name('statuses');
        Route::post('/refill', [OsemController::class, 'refill'])->name('refill');
        Route::post('/refills', [OsemController::class, 'refills'])->name('refills');
        Route::post('/refill-status', [OsemController::class, 'refillStatus'])->name('refill-status');
        Route::post('/refill-statuses', [OsemController::class, 'refillStatuses'])->name('refill-statuses');
        Route::post('/cancel', [OsemController::class, 'cancel'])->name('cancel');
        Route::post('/cancels', [OsemController::class, 'cancels'])->name('cancels');
        Route::post('/balance', [OsemController::class, 'balance'])->name('balance');
    });

    Route::prefix('reseller')->name('reseller.')->group(function () {
        Route::get('/catalog', [ResellerController::class, 'catalog'])->name('catalog');
        Route::get('/catalog-meta', [ResellerController::class, 'catalogMeta'])->name('catalog-meta');
        Route::get('/wallet', [ResellerController::class, 'wallet'])->name('wallet');
        Route::get('/topups', [ResellerController::class, 'topupRequests'])->name('topups');
        Route::get('/orders', [ResellerController::class, 'myOrders'])->name('orders');
        Route::get('/profit-report', [ResellerController::class, 'profitReport'])->name('profit-report');
        Route::post('/orders', [ResellerController::class, 'placeOrder'])->name('orders.place');
        Route::post('/topups', [ResellerController::class, 'createTopupRequest'])->name('topups.create');
    });
});

require __DIR__.'/auth.php';
