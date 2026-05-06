<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Transactions\ReportController;
use App\Http\Controllers\Transactions\TransactionController;
use App\Http\Controllers\Transactions\CategoryController;
use App\Http\Controllers\Transactions\RecurringController;
use App\Models\Category;

/*
|--------------------------------------------------------------------------
| AUTHENTICATED PAGES (INERTIA)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | REPORT PAGES
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/summary', fn () => Inertia::render('Reports/Summary'));
    Route::get('/reports/by-category', fn () => Inertia::render('Reports/ByCategory'));
    Route::get('/reports/annual', fn () => Inertia::render('Reports/Annual'));
    Route::get('/reports/category-summary', fn () => Inertia::render('Reports/CategorySummary'));
    Route::get('/reports/charity-year', fn () => Inertia::render('Reports/CharityYear'));
    Route::get('/reports', fn () =>
        Inertia::render('transactions/Reports', [
            'annualReport' => [],
            'categoryReport' => [],
            'year' => now()->year,
            'user' => auth()->user(),
        ])
    );

    /*
    |--------------------------------------------------------------------------
    | TRANSACTIONS (VIEW PAGE)
    |--------------------------------------------------------------------------
    */
    Route::get('/transactions', [TransactionController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES (VIEW PAGE)
    |--------------------------------------------------------------------------
    */
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/charity', fn () =>
        Inertia::render('transactions/Charity', [
            'charityStats' => [
                'expense' => 0,
                'transactions' => [],
            ],
            'categories' => Category::all(),
            'year' => now()->year,
        ])
    );
});


/*
|--------------------------------------------------------------------------
| ADMIN ONLY (CRUD + MANAGEMENT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CATEGORY CRUD (REPLACES apiResource)
    |--------------------------------------------------------------------------
    */

    Route::get('/categories/create', fn () =>
        Inertia::render('Categories/Create')
    );

    Route::get('/categories/{category}/edit', fn ($category) =>
        Inertia::render('Categories/Edit', [
            'category' => $category
        ])
    );

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::patch('/categories/{id}/restore', [CategoryController::class, 'restore']);
    Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete']);


    /*
    |--------------------------------------------------------------------------
    | TRANSACTION CRUD
    |--------------------------------------------------------------------------
    */

    Route::get('/transactions/trashed', fn () =>
        Inertia::render('Transactions/Trashed')
    );

    Route::get('/transactions/{transaction}/edit', fn ($transaction) =>
        Inertia::render('Transactions/Edit', [
            'transaction' => $transaction
        ])
    );

    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    Route::patch('/transactions/{id}/restore', [TransactionController::class, 'restore']);
    Route::delete('/transactions/{id}/force-delete', [TransactionController::class, 'forceDelete']);


    /*
    |--------------------------------------------------------------------------
    | RECURRING CRUD
    |--------------------------------------------------------------------------
    */

    Route::get('/recurring', fn () =>
        Inertia::render('transactions/Recurring', [
            'recurringTransactions' => \App\Models\Recurring::with('category')->get()
        ])
    );

    Route::post('/recurring', [RecurringController::class, 'store']);
    Route::put('/recurring/{recurring}', [RecurringController::class, 'update']);
    Route::delete('/recurring/{recurring}', [RecurringController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | REPORT ADMIN PAGES
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/other-year', fn () =>
        Inertia::render('Reports/OtherYear')
    );

    Route::get('/others', fn () =>
        Inertia::render('transactions/Others', [
            'otherStats' => [
                'expense' => 0,
                'transactions' => [],
            ],
            'categories' => Category::all(),
            'year' => now()->year,
        ])
    );

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::get('/users/create', fn () =>
        Inertia::render('Users/Create')
    );
});