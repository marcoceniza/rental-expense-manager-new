<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

// ── Admin Category CRUD ─────────────────────────────────────────────────────────

test('admin can view the categories index page', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk();
});

test('admin can create a category', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name'       => 'Utilities',
            'type'       => 'expense',
            'is_tuition' => false,
            'is_other'   => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'Utilities']);
});

test('admin can update a category', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category), [
            'name'       => 'New Name',
            'type'       => $category->type,
            'is_tuition' => $category->is_tuition,
            'is_other'   => $category->is_other,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
});

test('admin can delete a category with no transactions', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect();

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

test('admin cannot delete a category that has transactions', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();
    Transaction::factory()->create(['category_id' => $category->id, 'user_id' => $admin->id]);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect();

    $this->assertNotSoftDeleted('categories', ['id' => $category->id]);
});

test('admin can restore a soft-deleted category', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();
    $category->delete();

    $this->actingAs($admin)
        ->patch(route('admin.categories.restore', $category->id))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
});

test('admin can force-delete a category with no transactions', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();
    $category->delete();

    $this->actingAs($admin)
        ->delete(route('admin.categories.force-delete', $category->id))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('admin cannot force-delete a category that has transactions', function () {
    $admin    = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();
    Transaction::factory()->create(['category_id' => $category->id, 'user_id' => $admin->id]);
    $category->delete();

    $this->actingAs($admin)
        ->delete(route('admin.categories.force-delete', $category->id))
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

// ── Validation ──────────────────────────────────────────────────────────────────

test('creating a category requires a name and type', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [])
        ->assertSessionHasErrors(['name', 'type']);
});

// ── Guest access ────────────────────────────────────────────────────────────────

test('guest cannot access categories', function () {
    $this->get(route('admin.categories.index'))
        ->assertRedirect(route('login'));
});
