<?php

namespace App\Observers;

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
    }

    public function updated(Expense $expense): void
    {
        $this->handleVehicleOdometer($expense);
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
