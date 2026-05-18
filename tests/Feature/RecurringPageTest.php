<?php

use App\Models\Category;
use App\Models\Transaction;

it('builds category transaction description options for the recurring modal', function () {
    $category = Category::create([
        'name' => 'Utilities',
        'type' => 'expense',
        'is_tuition' => false,
        'is_other' => false,
    ]);

    Transaction::create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 2500,
        'transaction_date' => now(),
        'description' => 'Electricity Bill',
    ]);

    Transaction::create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 3000,
        'transaction_date' => now()->subWeek(),
        'description' => 'Water Bill',
    ]);

    $categories = Transaction::recurringCategoryOptions();

    expect($categories)->toHaveCount(1);
    expect($categories[0]['transaction_descriptions'])->toHaveCount(2);
    expect($categories[0]['transaction_descriptions'][0])->toMatchArray([
        'description' => 'Electricity Bill',
        'amount' => 2500,
    ]);
});
