<?php

use App\Enums\ReconciliationStatus;
use App\Models\BankAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BankAccount::class)->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->date('transaction_date');
            $table->string('vendor_name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('reconciliation_status')->default(ReconciliationStatus::Pending->value);
            $table->timestamps();
            $table->unique(['external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
