<?php

namespace Database\Factories;

use App\Enums\ReconciliationStatus;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        return [
            'bank_account_id' => BankAccount::factory(),
            'external_id' => $this->faker->uuid(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'vendor_name' => $this->faker->company(),
            'amount' => $this->faker->randomFloat(2, -500, -5), // Débits (négatifs)
            'currency' => 'EUR',
            'reconciliation_status' => ReconciliationStatus::Pending,
        ];
    }
}
