<?php

use App\Models\User;

test('admin registration screen can be rendered', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/register');

    $response->assertStatus(200);
});

test('admin can create a new user', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    $response->assertRedirect(route('admin.register', absolute: false));
});
