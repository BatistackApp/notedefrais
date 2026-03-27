<?php

namespace App\jobs;

use App\Models\BankAccount;
use App\Services\BridgeApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchBridgeAccountDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BankAccount $bankAccount) {}

    /**
     * Ce Job utilise la méthode de notre service BridgeApiService
     * pour enrichir le compte avec ses vraies informations (IBAN, Nom).
     */
    public function handle(BridgeApiService $bridgeApi): void
    {
        if (!$this->bankAccount->bridge_account_id) {
            return;
        }

        try {
            // Appel à l'API via le Service
            $details = $bridgeApi->getAccountDetails($this->bankAccount->bridge_account_id);

            // Mise à jour silencieuse en base de données
            $this->bankAccount->update([
                'name' => $details['name'] ?? $this->bankAccount->name,
                'iban' => $details['iban'] ?? $this->bankAccount->iban,
                'currency' => $details['currency'] ?? 'EUR',
            ]);

            Log::info("Détails du compte Bridge mis à jour pour le compte ID {$this->bankAccount->id}");

        } catch (\Exception $e) {
            Log::error("Échec de la récupération des détails du compte : " . $e->getMessage());
        }
    }
}
