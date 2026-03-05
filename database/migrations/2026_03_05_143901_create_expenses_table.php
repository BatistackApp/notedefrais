<?php

use App\Models\Category;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Category::class)->constrained();
            $table->foreignIdFor(Vehicle::class)->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('expensed_at');
            $table->decimal('amount_total', 15, 2);
            $table->decimal('tax_rate', 4, 2);
            $table->decimal('amount_taxe', 15, 2);
            $table->string('site_reference')->nullable();
            $table->string('status')->default(\App\Enums\ExpenseStatus::DRAFT->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
