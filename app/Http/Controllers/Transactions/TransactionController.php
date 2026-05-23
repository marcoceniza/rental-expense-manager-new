<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index()
    {
        return Inertia::render('transactions/Transactions', [
            'transactions' => Transaction::with('category')
                ->orderByDesc('transaction_date')
                ->orderByDesc('created_at')
                ->paginate(15),
            'categories' => Category::all(),
            'trashed' => Transaction::onlyTrashed()
                ->with('category')
                ->orderByDesc('transaction_date')
                ->orderByDesc('created_at')
                ->paginate(15),
            'trashedCount' => Transaction::onlyTrashed()->count(),
            'currentDate' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Display a listing of user's transactions
     */
    public function userTransactions()
    {
        return Inertia::render('transactions/Transactions', [
            'transactions' => Transaction::with('category')
                ->filtered(null, null, auth()->user())
                ->orderByDesc('transaction_date')
                ->orderByDesc('created_at')
                ->paginate(15),
            'categories' => Category::where('is_other', false)->get(),
            'trashed' => [],
            'trashedCount' => 0,
            'currentDate' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Store new transaction
     */
    public function store(StoreTransactionRequest $request)
    {
        Transaction::create($request->validated());

        return back()->with('success', 'Transaction created successfully.');
    }

    /**
     * Update transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());

        return back()->with('success', 'Transaction updated successfully.');
    }

    /**
     * Delete transaction
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Restore transaction
     */
    public function restore($id)
    {
        $transaction = Transaction::withTrashed()->findOrFail($id);
        $transaction->restore();

        return back()->with('success', 'Transaction restored successfully.');
    }

    /**
     * Force delete transaction
     */
    public function forceDelete($id)
    {
        $transaction = Transaction::withTrashed()->findOrFail($id);
        $transaction->forceDelete();

        return back()->with('success', 'Transaction permanently deleted.');
    }
}
