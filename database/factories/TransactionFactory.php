<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => \App\Models\Category::factory(),
            'type' => fake()->randomElement(['income', 'expense', 'liability']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'transaction_date' => fake()->date(),
            'description' => fake()->sentence(),
            'remarks' => fake()->optional()->sentence(),
            'is_recurring_generated' => false,
        ];
    }
}
