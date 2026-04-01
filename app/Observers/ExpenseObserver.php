<?php

namespace App\Observers;

use App\Enums\ExpenseStatus;
use App\jobs\ProcessAutoReconciliationJob;
use App\jobs\SealExpenseAttachmentsJob;
use App\Models\Expense;
use App\Services\VehicleService;

class ExpenseObserver
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    public function created(Expense $expense): void
    {
        $this->handleVehicleOdometer($expense);
        $expense->updateQuietly([
            'status' => ExpenseStatus::PENDING,
        ]);
    }

    public function updated(Expense $expense): void
    {
        $this->handleVehicleOdometer($expense);
        // Si l'employé vient de soumettre la note de frais, on lance le scellement asynchrone
        if ($expense->wasChanged('status') && $expense->status === 'submitted') {
            SealExpenseAttachmentsJob::dispatch($expense);
        }
    }

    public function saved(Expense $expense): void
    {
        if ($expense->isDirty('status') && $expense->status === ExpenseStatus::PENDING->value) {
            ProcessAutoReconciliationJob::dispatch();
        }
    }

    /**
     * Logique partagée pour mettre à jour l'odomètre si nécessaire.
     */
    protected function handleVehicleOdometer(Expense $expense): void
    {
        // On ne met à jour l'odomètre que si un véhicule est lié
        if ($expense->vehicle_id) {
            $this->vehicleService->updateOdometerFromExpense($expense);
        }
    }
}
