<?php

namespace Database\Factories;

use App\Models\BankTransaction;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        return [
            'external_id' => $this->faker->word(),
            'transaction_date' => Carbon::now(),
            'label' => $this->faker->word(),
            'amount_total' => $this->faker->randomFloat(),
            'currency' => $this->faker->word(),
            'is_reconciled' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'expense_id' => Expense::factory(),
        ];
    }
}
