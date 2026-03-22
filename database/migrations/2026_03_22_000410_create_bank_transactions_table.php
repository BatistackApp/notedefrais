<?php

use App\Models\BankTransaction;
use App\Models\Expense;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable();
            $table->date('transaction_date');
            $table->string('label');
            $table->decimal('amount_total');
            $table->string('currency')->default('EUR');
            $table->boolean('is_reconciled');
            $table->foreignIdFor(Expense::class)->nullable();
            $table->timestamps();
            $table->unique(['external_id']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignIdFor(BankTransaction::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_transaction_id');
        });
    }
};
