<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index()
    {
        return Inertia::render('transactions/Transactions', [
            'transactions' => Transaction::with('category')->latest()->paginate(15),
            'categories' => Category::all(),
            'trashed' => Transaction::onlyTrashed()->count(),
        ]);
    }

    /**
     * Store new transaction
     */
    public function store(StoreTransactionRequest $request)
    {
        Transaction::create($request->validated());

        return redirect('/transactions')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Update transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());

        return redirect('/transactions')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Delete transaction
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect('/transactions')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Restore transaction
     */
    public function restore($id)
    {
        $transaction = Transaction::withTrashed()->findOrFail($id);
        $transaction->restore();

        return redirect('/transactions')
            ->with('success', 'Transaction restored successfully.');
    }

    /**
     * Force delete transaction
     */
    public function forceDelete($id)
    {
        $transaction = Transaction::withTrashed()->findOrFail($id);
        $transaction->forceDelete();

        return redirect('/transactions')
            ->with('success', 'Transaction permanently deleted.');
    }
}