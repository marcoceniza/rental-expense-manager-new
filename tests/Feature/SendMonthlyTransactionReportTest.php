<?php

namespace Tests\Feature;

use App\Mail\MonthlyTransactionReport;
use App\Models\Category;
use App\Models\Recurring;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class SendMonthlyTransactionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_a_monthly_report_with_correct_data()
    {
        Mail::fake();
        // Set recipient
        config(['services.monthly_report_email' => 'test@example.com']);

        $today = now();

        $category = Category::factory()->create();
        $recurring = Recurring::create([
            'category_id' => $category->id,
            'amount' => 100.00,
            'frequency' => 'monthly',
            'start_date' => now(),
            'description' => 'Test recurring',
        ]);
        Transaction::factory()->create([
            'recurring_id' => $recurring->id,
            'transaction_date' => $today,
            'is_recurring_generated' => true,
        ]);

        Transaction::factory()->create([
            'transaction_date' => $today,
            'is_recurring_generated' => false,
        ]);

        Artisan::call('send-monthly-transaction-report');

        Mail::assertQueued(MonthlyTransactionReport::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && $mail->data['transactions']->count() === 2
                && $mail->data['recurring_count'] === 1
                && $mail->data['non_recurring_count'] === 1;
        });
        Mail::assertQueuedCount(1);
    }

    public function test_monthly_report_is_scheduled_for_the_last_day_of_the_month()
    {
        $reportEvent = collect(Schedule::events())->first(
            fn ($event) => str_contains($event->command, 'send-monthly-transaction-report')
        );

        expect($reportEvent)->not->toBeNull();
        expect($reportEvent->getExpression())->toBe(sprintf('55 23 %d * *', now()->endOfMonth()->day));
    }
}
