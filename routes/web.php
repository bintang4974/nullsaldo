<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Wallets
    Route::resource('wallets', WalletController::class);

    // Transactions
    Route::resource('transactions', TransactionController::class)->except(['index', 'show']);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Statistics
    Route::get('/statistics', [StatisticController::class, 'index'])->name('statistics.index');

    // Budget routes
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::put('/budgets/{category}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{category}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::post('/budgets/copy', [BudgetController::class, 'copyFromPreviousMonth'])->name('budgets.copy');

    // Export routes
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::post('/export/pdf', [ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::post('/export/excel', [ExportController::class, 'exportExcel'])->name('export.excel');

    // AI Chat routes
    Route::get('/ai-assistant', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-assistant/chat', [AiChatController::class, 'chat'])->name('ai-chat.send');
    Route::get('/ai-assistant/summary', [AiChatController::class, 'getSummary'])->name('ai-chat.summary');
});

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__ . '/auth.php';
