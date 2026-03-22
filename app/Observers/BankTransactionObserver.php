<?php

namespace App\Observers;

use App\jobs\AttemptSingleReconciliationJob;
use App\Models\BankTransaction;

class BankTransactionObserver
{
    public function created(BankTransaction $transaction): void
    {
        AttemptSingleReconciliationJob::dispatch($transaction);
    }
}
