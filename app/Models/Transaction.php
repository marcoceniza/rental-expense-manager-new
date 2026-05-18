<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
}
