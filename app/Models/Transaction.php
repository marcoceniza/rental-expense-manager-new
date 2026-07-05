<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'transaction_date',
        'description',
        'remarks',
        'recurring_id',
        'is_recurring_generated',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'transaction_date' => 'date',
        'is_recurring_generated' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeFiltered($query, $start = null, $end = null, $user = null)
    {
        $user = $user ?? auth()->user();

        $query->with('category');

        if ($start && $end) {
            $query->whereBetween('transaction_date', [$start, $end]);
        }

        if (! $user->isAdmin()) {
            $query->whereHas('category', function ($q) {
                $q->where('is_other', false);
            });
        }

        return $query;
    }

    public static function recurringCategoryOptions(): Collection
    {
        return self::with('category')
            ->orderByDesc('transaction_date')
            ->get()
            ->filter(fn ($transaction) => $transaction->category)
            ->groupBy(fn ($transaction) => $transaction->category_id)
            ->map(function ($transactions) {
                $category = $transactions->first()->category;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'transaction_descriptions' => $transactions
                        ->map(fn ($transaction) => [
                            'description' => $transaction->description,
                            'amount' => $transaction->amount,
                        ])
                        ->unique(fn ($transaction) => $transaction['description'])
                        ->values(),
                ];
            })
            ->values();
    }
}
