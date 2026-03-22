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

class CheckDuplicateExpensesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Expense $expense) {}

    public function handle(): void
    {
        /**
         * Recherche du premier doublon potentiel.
         * On utilise first() au lieu de get() pour optimiser la requête SQL
         * et éviter les problèmes de méthodes sur les collections.
         */
        $duplicate = Expense::where('id', '!=', $this->expense->id)
            ->where('amount_total', $this->expense->amount_total)
            ->whereDate('expensed_at', $this->expense->expensed_at)
            ->whereIn('status', [ExpenseStatus::PENDING, ExpenseStatus::APPROVED])
            ->first();

        if ($duplicate) {
            // On marque la dépense en description pour alerter l'admin
            $this->expense->update([
                'description' => $this->expense->description."\n\n⚠️ ATTENTION : Doublon potentiel détecté avec la dépense #".$duplicate->id,
            ]);

            Log::warning("Doublon potentiel détecté pour la dépense #{$this->expense->id} (conflit avec #{$duplicate->id})");
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
            description: 'Le job CheckDuplicateExpenseJob à échoué'
        );
    }
}
