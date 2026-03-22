<?php

use App\Enums\ReconciliationStatus;
use App\Models\BankTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignIdFor(BankTransaction::class)->nullable()->constrained()->nullOnDelete();
            $table->string('reconciliation_status')->default(ReconciliationStatus::Pending->value);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeignIdFor(BankTransaction::class);
            $table->dropColumn('reconciliation_status');
        });
    }
};
