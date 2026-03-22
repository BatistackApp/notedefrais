<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'name' => 'Carte Affaires - ' . $this->faker->name(),
            'iban' => $this->faker->iban('FR'),
            'currency' => 'EUR',
            'is_active' => true,
        ];
    }
}
