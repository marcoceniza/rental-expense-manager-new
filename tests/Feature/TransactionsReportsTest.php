<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

it('keeps the main transactions list at 15 visible rows per page', function () {
    $user = User::factory()->create(['user_type' => 'admin']);

    $specialCategory = Category::factory()->create(['is_tuition' => true]);
    $otherCategory = Category::factory()->create(['is_other' => true]);

    for ($i = 1; $i <= 15; $i++) {
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::factory()->create()->id,
            'type' => 'expense',
            'description' => "Normal transaction {$i}",
            'transaction_date' => now()->subDays($i)->toDateString(),
        ]);
    }

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $specialCategory->id,
        'type' => 'expense',
        'description' => 'Tuition transaction',
        'transaction_date' => now()->toDateString(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $otherCategory->id,
        'type' => 'income',
        'description' => 'Other transaction',
        'transaction_date' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin/transactions');
    $response->assertOk();

    $content = $response->getContent();
    $normalMatches = preg_match_all('/Normal transaction \d+/', $content, $matches);

    expect($normalMatches)->toBe(15);
    expect($content)->not->toContain('Tuition transaction');
    expect($content)->not->toContain('Other transaction');
});

it('loads the related user for charity and others transactions', function () {
    $user = User::factory()->create(['user_type' => 'admin']);

    $category = Category::factory()->create(['is_tuition' => true]);
    $othersCategory = Category::factory()->create(['is_other' => true]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'description' => 'Tuition payment',
        'transaction_date' => '2026-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $othersCategory->id,
        'type' => 'income',
        'description' => 'Other income',
        'transaction_date' => '2026-02-15',
    ]);

    $tuitionTransaction = Transaction::where('description', 'Tuition payment')->firstOrFail();
    expect($tuitionTransaction->load('user')->user->name)->toBe($user->name);

    $this->actingAs($user);

    $this->get('/admin/charity?year=2026')->assertOk();
    $this->get('/admin/others?year=2026')->assertOk();
});
