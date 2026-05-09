<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Transactions\CategoryController;
use App\Http\Controllers\Transactions\RecurringController;
use App\Http\Controllers\Transactions\ReportController;
use App\Http\Controllers\Transactions\TransactionController;
use App\Models\Recurring;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'user'])->group(function () {

    Route::get('/dashboard', [ReportController::class, 'userDashboard'])
        ->name('dashboard');

    Route::get('/reports', [ReportController::class, 'userAnnualReport'])
        ->name('reports');

    Route::get('/transactions', [TransactionController::class, 'userTransactions'])
        ->name('transactions.user');

    Route::get('/charity', [ReportController::class, 'userCharityReport'])
        ->name('charity');

});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [ReportController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/charity', [ReportController::class, 'charityIndex'])
        ->name('admin.charity');
    Route::get('/others', [ReportController::class, 'otherIndex'])
        ->name('admin.others');
    Route::get('/reports', [ReportController::class, 'annualReport'])
        ->name('admin.reports');

    Route::resource('categories', CategoryController::class);

    Route::resource('transactions', TransactionController::class);

    Route::prefix('categories')->group(function () {
        Route::patch('{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('trashed', fn () => Inertia::render('Transactions/Trashed'));

        Route::patch('{id}/restore', [TransactionController::class, 'restore'])->name('transactions.restore');

        Route::delete('{id}/force-delete', [TransactionController::class, 'forceDelete'])->name('transactions.force-delete');
    });

    Route::get('/recurring', fn () => Inertia::render('transactions/Recurring', [
        'recurringTransactions' => Recurring::with('category')->get(),
    ]))->name('recurring.index');

    Route::post('/recurring', [RecurringController::class, 'store'])->name('recurring.store');

    Route::put('/recurring/{recurring}', [RecurringController::class, 'update'])->name('recurring.update');

    Route::delete('/recurring/{recurring}', [RecurringController::class, 'destroy'])->name('recurring.destroy');

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);

});
