<?php

namespace App\Console\Commands;

use App\Mail\MonthlyTransactionReport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlyTransactionReport extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'send-monthly-transaction-report';

    /**
     * Command description
     */
    protected $description = 'Send a monthly email report of all transactions';

    public function handle(): int
    {
        $now = Carbon::now();
        $first = $now->copy()->firstOfMonth();
        $last = $now->copy()->lastOfMonth();

        $transactions = Transaction::query()
            ->whereBetween('transaction_date', [$first, $last])
            ->with('category')
            ->get();

        $totals = $transactions->groupBy('type')->map(function ($group) {
            return $group->sum('amount');
        });

        $recurringCount = $transactions->where('is_recurring_generated', true)->count();
        $nonRecurringCount = $transactions->where('is_recurring_generated', false)->count();

        $recipient = config('services.monthly_report_email');

        if (! $recipient) {
            $this->error('MONTHLY_REPORT_EMAIL not configured. Aborting.');

            return self::FAILURE;
        }

        Mail::to($recipient)->send(new MonthlyTransactionReport([
            'month' => $now->format('F Y'),
            'transactions' => $transactions,
            'totals' => $totals,
            'recurring_count' => $recurringCount,
            'non_recurring_count' => $nonRecurringCount,
        ]));

        $this->info("Monthly transaction report sent to {$recipient}");

        return self::SUCCESS;
    }
}
