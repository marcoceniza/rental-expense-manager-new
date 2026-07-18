<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'is_tuition',
        'is_other',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_tuition' => 'boolean',
        'is_other' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringTransactions()
    {
        return $this->hasMany(Recurring::class);
    }
}
