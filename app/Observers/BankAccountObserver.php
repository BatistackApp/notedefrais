<?php

namespace App\Observers;

use App\jobs\FetchBridgeAccountDetailsJob;
use App\jobs\SyncBridgeTransactionsJob;
use App\Models\BankAccount;

class BankAccountObserver
{
    public function updated(BankAccount $bankAccount): void
    {
        // Si l'ID Bridge vient d'être renseigné (nouvelle connexion réussie)
        if ($bankAccount->wasChanged('bridge_account_id') && !empty($bankAccount->bridge_account_id)) {

            // 1. On lance le Job pour récupérer le nom réel et l'IBAN
            FetchBridgeAccountDetailsJob::dispatch($bankAccount);

            // 2. On lance une première synchronisation immédiate des transactions
            SyncBridgeTransactionsJob::dispatch();
        }
    }
}
