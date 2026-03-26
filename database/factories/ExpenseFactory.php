<?php

namespace Database\Factories;

use App\Enums\DigitalSealStatus;
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

    /**
     * Indique que la note de frais est scellée numériquement.
     */
    public function sealed(): static
    {
        return $this->state(fn (array $attributes) => [
            'digital_seal_status' => DigitalSealStatus::Sealed,
            'sealed_at' => now(),
            'status' => 'submitted', // Une dépense scellée est forcément soumise
        ]);
    }

    /**
     * Indique que le fichier de la note de frais a été altéré (fraude/erreur).
     */
    public function compromised(): static
    {
        return $this->state(fn (array $attributes) => [
            'digital_seal_status' => DigitalSealStatus::Compromised,
            'sealed_at' => now()->subDays(2),
        ]);
    }
}
