<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TableSimulationController;
use App\Http\Controllers\CustomerOrderController;

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

// Simulasi Meja & Pemesanan Mandiri (QR Code) - Public Routes
Route::get('/simulasi-meja', [TableSimulationController::class, 'index'])->name('simulasi-meja');
Route::get('/order/{table_number}', [CustomerOrderController::class, 'index'])->name('order.index');
Route::post('/order/checkout', [CustomerOrderController::class, 'store'])->name('order.store');
Route::get('/order/{table_number}/success/{transaction}', [CustomerOrderController::class, 'success'])->name('order.success');

// Tracking Pesanan
Route::get('/tracking/{invoice_number}', [\App\Http\Controllers\TrackingController::class, 'show'])->name('tracking.show');

// Simulasi QRIS Payment (Mock Bank Page)
Route::get('/qris/pay/{token}', [\App\Http\Controllers\QrisPaymentController::class, 'show'])->name('qris.pay');
Route::post('/qris/pay/{token}', [\App\Http\Controllers\QrisPaymentController::class, 'confirm'])->name('qris.confirm');
Route::get('/qris/success/{token}', [\App\Http\Controllers\QrisPaymentController::class, 'success'])->name('qris.success');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // POS Routes
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [\App\Http\Controllers\PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/orders', [\App\Http\Controllers\PosController::class, 'orders'])->name('pos.orders');
    Route::patch('/pos/orders/{transaction}', [\App\Http\Controllers\PosController::class, 'updateOrderStatus'])->name('pos.updateStatus');

    // Keuangan Routes
    Route::get('/finance/income', [\App\Http\Controllers\FinanceController::class, 'income'])->name('finance.income');
    Route::get('/finance/income/export', [\App\Http\Controllers\FinanceController::class, 'exportIncome'])->name('finance.income.export');
    Route::get('/finance/income/export-pdf', [\App\Http\Controllers\FinanceController::class, 'exportIncomePdf'])->name('finance.income.export.pdf');
    
    Route::get('/finance/expenses', [\App\Http\Controllers\FinanceController::class, 'expenses'])->name('finance.expenses');
    Route::post('/finance/expenses', [\App\Http\Controllers\FinanceController::class, 'storeExpense'])->name('finance.expenses.store');
    Route::get('/finance/expenses/export', [\App\Http\Controllers\FinanceController::class, 'exportExpenses'])->name('finance.expenses.export');
    Route::get('/finance/expenses/export-pdf', [\App\Http\Controllers\FinanceController::class, 'exportExpensesPdf'])->name('finance.expenses.export.pdf');

    // Pemesanan Bahan Baku Invoice
    Route::get('/purchases/{purchase}/invoice', [\App\Http\Controllers\PurchaseInvoiceController::class, 'show'])->name('purchases.invoice');
});

require __DIR__.'/auth.php';