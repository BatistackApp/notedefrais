<?php

namespace App\Observers;

use App\jobs\FetchBridgeAccountDetailsJob;
use App\jobs\SyncBridgeTransactionsJob;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Log;

class BankAccountObserver
{
    public function updated(BankAccount $bankAccount): void
    {
        Log::alert('Aucun changement de bank account');
        // Si l'ID Bridge vient d'être renseigné (nouvelle connexion réussie)
        if ($bankAccount->wasChanged('bridge_item_id')) {
            Log::alert('Changement de bridge_item_id');
            // 1. On lance le Job pour récupérer le nom réel et l'IBAN
            FetchBridgeAccountDetailsJob::dispatch($bankAccount);

            // 2. On lance une première synchronisation immédiate des transactions
            SyncBridgeTransactionsJob::dispatch();
        }
    }
}
