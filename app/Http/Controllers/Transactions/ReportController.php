<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Category;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $month = request('month', now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        return Inertia::render('Dashboard', [
            'month' => $monthDate->format('Y-m'),
            'categories' => Category::all(),
            'monthlyReport' => $this->buildMonthlyReport($start, $end),
            'transactions' => $this->buildMonthlyTransactions($start, $end),
            'annualData' => $this->buildYearData($monthDate->year),
        ]);
    }

    /**
     * Central query (reusable logic stays here)
     */
    private function baseTransactionQuery($start, $end)
    {
        $query = Transaction::with('category')
            ->whereBetween('transaction_date', [$start, $end]);

        if (auth()->user()->user_type === 'user') {
            $query->whereHas('category', function ($q) {
                $q->where('is_other', false);
            });
        }

        return $query;
    }

    /**
     * Shared year builder (NO JSON anymore)
     */
    private function buildYearData($year)
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $query = $this->baseTransactionQuery($start, $end);

        $expense = (clone $query)->sum('amount');

        $transactions = (clone $query)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->get();

        return [
            'expense' => $expense,
            'transactions' => $transactions,
        ];
    }

    private function buildMonthlyReport($start, $end)
    {
        $query = $this->baseTransactionQuery($start, $end);

        $income = (clone $query)->where('type', 'income')->sum('amount');
        $expense = (clone $query)->where('type', 'expense')->sum('amount');
        $liability = (clone $query)->where('type', 'liability')->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'liability' => $liability,
            'net' => $income - ($expense + $liability),
        ];
    }

    private function buildMonthlyTransactions($start, $end)
    {
        return $this->baseTransactionQuery($start, $end)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->get();
    }

    /**
     * Optional helper: annual summary logic
     */
    public function getAnnualData($year)
    {
        return collect(range(1, 12))->map(function ($month) use ($year) {

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            $query = $this->baseTransactionQuery($start, $end);

            $income = (clone $query)->where('type', 'income')->sum('amount');
            $expense = (clone $query)->where('type', 'expense')->sum('amount');
            $liability = (clone $query)->where('type', 'liability')->sum('amount');

            return [
                'month' => $start->format('F'),
                'income' => $income,
                'expense' => $expense,
                'liability' => $liability,
                'net' => $income - ($expense + $liability),
            ];
        });
    }
}