<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $month = $request->query('month');
        $type = $request->query('type');

        $query = Transaction::with(['category', 'user'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        if (in_array($type, ['income', 'expense', 'liability'], true)) {
            $query->where('type', $type);
        }

        if (is_numeric($month) && ((int) $month >= 1 && (int) $month <= 12)) {
            $transactions = $query->whereMonth('transaction_date', (int) $month)->get();
        } else {
            $transactions = $query->paginate(15);
        }

        return Inertia::render('transactions/Transactions', [
            'transactions' => $this->formatTransactionPayload($transactions),
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
    public function userTransactions(Request $request)
    {
        $month = $request->query('month');
        $type = $request->query('type');

        $query = Transaction::with('category')
            ->filtered(null, null, auth()->user())
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        if (in_array($type, ['income', 'expense', 'liability'], true)) {
            $query->where('type', $type);
        }

        if (is_numeric($month) && ((int) $month >= 1 && (int) $month <= 12)) {
            $transactions = $query->whereMonth('transaction_date', (int) $month)->get();
        } else {
            $transactions = $query->paginate(15);
        }

        return Inertia::render('transactions/Transactions', [
            'transactions' => $this->formatTransactionPayload($transactions),
            'categories' => Category::where('is_other', false)->get(),
            'trashed' => [],
            'trashedCount' => 0,
            'currentDate' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Format transaction payload for Inertia.
     */
    private function formatTransactionPayload(LengthAwarePaginator|Collection $transactions): array
    {
        if ($transactions instanceof LengthAwarePaginator) {
            return $transactions->toArray();
        }

        return [
            'data' => $transactions->values()->all(),
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $transactions->count(),
            'total' => $transactions->count(),
            'prev_page_url' => null,
            'next_page_url' => null,
            'links' => [],
        ];
    }

    /**
     * Store new transaction
     */
    public function store(StoreTransactionRequest $request)
    {
        Transaction::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

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
