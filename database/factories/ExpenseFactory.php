<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'expensed_at' => Carbon::now(),
            'amount_total' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'amount_taxe' => $this->faker->randomFloat(),
            'site_reference' => $this->faker->word(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
