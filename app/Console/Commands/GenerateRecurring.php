<?php

namespace App\Console\Commands;

use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurring extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'generate-recurring';

    /**
     * Command description
     */
    protected $description = 'Generate recurring transactions for the current month';

    /**
     * Execute the command
     */
    public function handle()
    {
        $today = Carbon::today();

        $recurrings = Recurring::with('category')->get();

        foreach ($recurrings as $recurring) {
            $startDate = Carbon::parse($recurring->start_date);

            // Skip inactive recurrings
            if (! $recurring->is_active) {
                continue;
            }

            // Do not generate before the recurring's start date
            if ($today->lt($startDate->startOfDay())) {
                continue;
            }

            // Do not generate after the recurring's end date if column exists
            if (! is_null($recurring->getAttribute('end_date')) && $today->gt(Carbon::parse($recurring->end_date)->endOfDay())) {
                continue;
            }

            $alreadyGenerated = Transaction::query()
                ->where('recurring_id', $recurring->id)
                ->whereMonth('transaction_date', $today->month)
                ->whereYear('transaction_date', $today->year)
                ->where('is_recurring_generated', true)
                ->exists();

            if ($alreadyGenerated) {
                continue;
            }

            // Only generate on or after the day-of-month the recurring is set to fire
            if ($today->day < $startDate->day) {
                continue;
            }

            Transaction::create([
                'category_id' => $recurring->category_id,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'transaction_date' => $today,
                'type' => $recurring->category?->type ?? 'expense',
                'recurring_id' => $recurring->id,
                'is_recurring_generated' => true,
            ]);
        }

        $this->info('Recurring transactions generated successfully.');
    }
}
