<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->withSession(['_token' => 'test_token'])
        ->post('/login', [
            '_token' => 'test_token',
            'email' => $user->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('admin users are redirected to the admin dashboard after login', function () {
    $admin = User::factory()->create([
        'user_type' => 'admin',
    ]);

    $response = $this->withSession(['_token' => 'test_token'])
        ->post('/login', [
            '_token' => 'test_token',
            'email' => $admin->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->withSession(['_token' => 'test_token'])
        ->post('/login', [
            '_token' => 'test_token',
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test_token'])
        ->post('/logout', [
            '_token' => 'test_token',
        ]);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
});
