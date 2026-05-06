<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;

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

        $recurrings = Recurring::query()->get();

        foreach ($recurrings as $recurring) {

            $alreadyGenerated = Transaction::query()
                ->where('recurring_id', $recurring->id)
                ->whereMonth('transaction_date', $today->month)
                ->whereYear('transaction_date', $today->year)
                ->where('is_recurring_generated', true)
                ->exists();

            if ($alreadyGenerated) {
                continue;
            }

            if ($today->day < Carbon::parse($recurring->start_date)->day) {
                continue;
            }

            Transaction::create([
                'category_id' => $recurring->category_id,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'transaction_date' => $today,
                'recurring_id' => $recurring->id,
                'is_recurring_generated' => true,
            ]);
        }

        $this->info('Recurring transactions generated successfully.');
    }
}