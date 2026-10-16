<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class MonthlyTransactionReport extends Mailable implements ShouldQueue
{
    use Queueable;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): self
    {
        $subject = 'Monthly Transaction Report – '.$this->data['month'];
        $view = 'emails.monthly-transaction-report';

        // Build CSV content
        $csv = "Transaction ID,Date,Category,Type,Amount,Recurring\n";
        foreach ($this->data['transactions'] as $tx) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%.2f,%s\n",
                $tx->id,
                $tx->transaction_date->format('Y-m-d'),
                $tx->category ? $tx->category->name : 'N/A',
                $tx->type,
                $tx->amount,
                $tx->is_recurring_generated ? 'Yes' : 'No'
            );
        }
        $csvPath = tempnam(sys_get_temp_dir(), 'monthly_report_').'.csv';
        file_put_contents($csvPath, $csv);

        return $this->subject($subject)
            ->view($view)
            ->with($this->data)
            ->attach($csvPath, [
                'mime' => 'text/csv',
                'as' => 'monthly_transactions_'.$this->data['month'].'.csv',
            ]);
    }
}
