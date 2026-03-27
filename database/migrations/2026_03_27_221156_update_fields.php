<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // L'ID de l'Item correspond à la connexion bancaire (ex: "La connexion Qonto de l'entreprise")
            $table->string('bridge_item_id')->nullable()->unique()->after('iban')->comment('ID de la connexion bancaire chez Bridge');

            // L'ID du compte correspond au sous-compte spécifique (ex: "Compte Courant Principal")
            $table->string('bridge_account_id')->nullable()->unique()->after('bridge_item_id')->comment('ID du compte spécifique chez Bridge');

            // Horodatage pour savoir quand on a synchronisé les transactions pour la dernière fois
            $table->timestamp('last_synced_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['bridge_item_id', 'bridge_account_id', 'last_synced_at']);
        });
    }
};
