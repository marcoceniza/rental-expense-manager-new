<?php

use App\Models\User;

test('authenticated users with the user role can access the user dashboard routes', function () {
    $user = User::factory()->create([
        'user_type' => 'user',
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')->assertStatus(200);
    $this->get('/reports')->assertStatus(200);
    $this->get('/transactions')->assertStatus(200);
    $this->get('/charity')->assertStatus(200);
});
