<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        return Inertia::render('Dashboard', $this->dashboardData($request));
    }

    public function userDashboard(Request $request)
    {
        return Inertia::render('Dashboard', $this->dashboardData($request));
    }

    private function dashboardData(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');

        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        return [
            'month' => $monthDate->format('Y-m'),
            'categories' => Category::all(),
            'monthlyReport' => $this->buildMonthlyReport($start, $end),
            'transactions' => Transaction::filtered($start, $end, auth()->user())
                ->orderByDesc('transaction_date')
                ->orderByDesc('created_at')
                ->get(),
        ];
    }

    private function buildMonthlyReport($start, $end)
    {
        $query = Transaction::filtered($start, $end, auth()->user());

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

    /*
    |--------------------------------------------------------------------------
    | ANNUAL REPORTS
    |--------------------------------------------------------------------------
    */

    public function userAnnualReport(Request $request)
    {
        return $this->annualReport($request);
    }

    public function annualReport(Request $request)
    {
        $year = $request->year ?? now()->year;

        return Inertia::render('transactions/Reports', [
            'annualReport' => $this->getAnnualData($year),
            'year' => (int) $year,
            'user' => auth()->user(),
            'categories' => Category::all(),
            'categorySummary' => $this->categorySummary($request),
        ]);
    }

    public function getAnnualData($year)
    {
        $monthly = collect(range(1, 12))->map(function ($month) use ($year) {

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            $query = Transaction::filtered($start, $end, auth()->user());

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

        return [
            'monthly' => $monthly,
            'totals' => [
                'income' => $monthly->sum('income'),
                'expense' => $monthly->sum('expense'),
                'liability' => $monthly->sum('liability'),
                'net' => $monthly->sum('net'),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY SUMMARY
    |--------------------------------------------------------------------------
    */

    public function categorySummary(Request $request)
    {
        $year = $request->year ?? now()->year;

        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $query = Transaction::filtered($start, $end, auth()->user());

        $categoryReport = $query->get()
            ->groupBy('category.name')
            ->map(function ($items, $categoryName) {

                $first = $items->first();

                return [
                    'name' => $categoryName,
                    'type' => $first->category->type ?? 'unknown',
                    'total' => $items->sum('amount'),
                ];
            })
            ->values();

        return [
            'categoryReport' => $categoryReport,
            'year' => (int) $year,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CHARITY
    |--------------------------------------------------------------------------
    */

    public function userCharityReport(Request $request)
    {
        return $this->charityIndex($request);
    }

    public function charityIndex(Request $request)
    {
        $year = $request->year ?? now()->year;

        return Inertia::render('transactions/Charity', [
            'charityStats' => $this->charityYearData($year),
            'categories' => Category::where('is_tuition', true)->get(),
            'year' => (int) $year,
        ]);
    }

    private function charityYearData($year)
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $query = Transaction::filtered($start, $end, auth()->user())
            ->where('type', 'expense')
            ->whereHas('category', function ($q) {
                $q->where('is_tuition', true);
            })
            ->orderByDesc('transaction_date');

        return [
            'expense' => (clone $query)->sum('amount'),
            'transactions' => $query->paginate(10),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OTHERS
    |--------------------------------------------------------------------------
    */

    public function otherIndex(Request $request)
    {
        $year = $request->year ?? now()->year;

        return Inertia::render('transactions/Others', [
            'otherStats' => $this->otherYearData($year),
            'categories' => Category::where('is_other', true)->get(),
            'year' => (int) $year,
        ]);
    }

    private function otherYearData($year)
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $query = Transaction::filtered($start, $end, auth()->user())
            ->where('type', 'income')
            ->whereHas('category', function ($q) {
                $q->where('is_other', true);
            })
            ->orderByDesc('transaction_date');

        return [
            'income' => (clone $query)->sum('amount'),
            'transactions' => $query->paginate(10),
        ];
    }
}
