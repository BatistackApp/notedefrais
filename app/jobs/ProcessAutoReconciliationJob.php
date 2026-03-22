<?php

namespace App\jobs;

use App\Services\BankReconciliationService;
use App\Services\ErrorHandlerToJiraService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAutoReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BankReconciliationService $service): void
    {
        $service->autoReconcilePendingTransactions();
    }

    /**
     * @throws \Exception
     */
    public function failed(\Throwable $exception): void
    {
        app(ErrorHandlerToJiraService::class)->handle(
            exception: $exception,
            summary: '[Job Failed]: Erreur lors du processus automatique',
            description: 'Le job ProcessAutoReconciliationJob à échoué'
        );
    }
}
