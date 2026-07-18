<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

// ── Admin Transaction CRUD ──────────────────────────────────────────────────────

test('admin can view the transactions index page', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertOk();
});

test('admin can store a transaction', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.transactions.store'), [
            'category_id'      => $category->id,
            'type'             => 'expense',
            'amount'           => 250.00,
            'transaction_date' => '2026-07-01',
            'description'      => 'Office supplies',
            'remarks'          => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'category_id' => $category->id,
        'description' => 'Office supplies',
        'amount'      => 250.00,
    ]);
});

test('admin can update a transaction', function () {
    $admin       = User::factory()->create(['user_type' => 'admin']);
    $transaction = Transaction::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($admin)
        ->put(route('admin.transactions.update', $transaction), [
            'category_id'      => $transaction->category_id,
            'type'             => 'income',
            'amount'           => 999.00,
            'transaction_date' => '2026-07-10',
            'description'      => 'Updated description',
            'remarks'          => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'id'          => $transaction->id,
        'description' => 'Updated description',
        'amount'      => 999.00,
    ]);
});

test('admin can soft-delete a transaction', function () {
    $admin       = User::factory()->create(['user_type' => 'admin']);
    $transaction = Transaction::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($admin)
        ->delete(route('admin.transactions.destroy', $transaction))
        ->assertRedirect();

    $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
});

test('admin can restore a soft-deleted transaction', function () {
    $admin       = User::factory()->create(['user_type' => 'admin']);
    $transaction = Transaction::factory()->create(['user_id' => $admin->id]);
    $transaction->delete();

    $this->actingAs($admin)
        ->patch(route('admin.transactions.restore', $transaction->id))
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'id'         => $transaction->id,
        'deleted_at' => null,
    ]);
});

test('admin can force-delete a transaction', function () {
    $admin       = User::factory()->create(['user_type' => 'admin']);
    $transaction = Transaction::factory()->create(['user_id' => $admin->id]);
    $transaction->delete();

    $this->actingAs($admin)
        ->delete(route('admin.transactions.force-delete', $transaction->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

// ── Validation ──────────────────────────────────────────────────────────────────

test('storing a transaction requires description and valid category', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.transactions.store'), [])
        ->assertSessionHasErrors(['category_id', 'type', 'amount', 'transaction_date', 'description']);
});

// ── User Transaction Access ─────────────────────────────────────────────────────

test('user can view their own transactions page', function () {
    $user = User::factory()->create(['user_type' => 'user']);

    $this->actingAs($user)
        ->get(route('transactions.user'))
        ->assertOk();
});

test('user cannot access admin transactions store', function () {
    $user     = User::factory()->create(['user_type' => 'user']);
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.transactions.store'), [
            'category_id'      => $category->id,
            'type'             => 'expense',
            'amount'           => 50.00,
            'transaction_date' => '2026-07-15',
            'description'      => 'Groceries',
            'remarks'          => null,
        ])
        ->assertForbidden();
});

test('guest cannot access transactions', function () {
    $this->get(route('admin.transactions.index'))
        ->assertRedirect(route('login'));
});
