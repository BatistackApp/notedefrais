<?php

namespace App\jobs;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Services\ErrorHandlerToJiraService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CleanupDraftExpensesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // On cible les brouillons créés il y a plus de 60 jours
        $threshold = now()->subDays(60);

        $deletedCount = Expense::where('status', ExpenseStatus::DRAFT)
            ->where('created_at', '<', $threshold)
            ->forceDelete(); // Suppression définitive

        if ($deletedCount > 0) {
            Log::info("Nettoyage automatique effectué. {$deletedCount} brouillons supprimés.");
        }
    }

    /**
     * @throws \Exception
     */
    public function failed(\Throwable $exception): void
    {
        app(ErrorHandlerToJiraService::class)->handle(
            exception: $exception,
            summary: '[Job Failed]: Erreur lors du processus automatique',
            description: 'Le job CleanupDraftExpenseJob à échoué'
        );
    }
}
