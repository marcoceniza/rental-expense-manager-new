<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('selecting a month returns all transactions for that month without pagination', function () {
    $admin = User::factory()->create([
        'user_type' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Rent',
        'type' => 'expense',
        'is_tuition' => false,
        'is_other' => false,
    ]);

    Transaction::create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 1000,
        'transaction_date' => now()->startOfMonth(),
        'description' => 'January Rent',
    ]);

    Transaction::create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 1200,
        'transaction_date' => now()->startOfMonth()->addDays(5),
        'description' => 'January Water',
    ]);

    Transaction::create([
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 1500,
        'transaction_date' => now()->subMonth(),
        'description' => 'Last Month Rent',
    ]);

    $response = $this->actingAs($admin, 'web')
        ->get('http://127.0.0.1:8000/admin/transactions?month=' . now()->format('m'));

    $response->assertStatus(200)
        ->assertInertia(function (AssertableInertia $page) {
            $page->component('transactions/Transactions')
                ->where('transactions.total', 2)
                ->where('transactions.last_page', 1)
                ->where('transactions.per_page', 2)
                ->where('transactions.links', []);
        });
});

test('pagination works when no month is selected and page parameter is used', function () {
    $admin = User::factory()->create([
        'user_type' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Utilities',
        'type' => 'expense',
        'is_tuition' => false,
        'is_other' => false,
    ]);

    for ($i = 1; $i <= 35; $i++) {
        Transaction::create([
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 1000 + $i,
            'transaction_date' => now()->subDays($i),
            'description' => "Transaction {$i}",
        ]);
    }

    foreach ([1, 2, 3] as $page) {
        $response = $this->actingAs($admin, 'web')
            ->get("http://127.0.0.1:8000/admin/transactions?page={$page}");

        $response->assertStatus(200)
            ->assertInertia(function (AssertableInertia $pageResponse) use ($page) {
                $pageResponse->component('transactions/Transactions')
                    ->where('transactions.current_page', $page)
                    ->where('transactions.last_page', 3)
                    ->where('transactions.total', 35)
                    ->has('transactions.links');
            });
    }
});
