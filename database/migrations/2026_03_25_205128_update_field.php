<?php

use App\Enums\DigitalSealStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('digital_seal_status')->default(DigitalSealStatus::Unsealed->value);
            $table->timestamp('sealed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['digital_seal_status', 'sealed_at']);
        });
    }
};
