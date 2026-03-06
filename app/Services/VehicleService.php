<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Vehicle;

/**
 * Service gérant la logique liée à la flotte de véhicules.
 */
class VehicleService
{
    /**
     * Met à jour l'odomètre d'un véhicule suite à un plein ou entretien.
     */
    public function updateOdometerFromExpense(Expense $expense): void
    {
        if (! $expense->vehicle_id) {
            return;
        }

        // Chargement de la relation si nécessaire
        $vehicle = $expense->vehicle ?? Vehicle::find($expense->vehicle_id);

        if (! $vehicle) {
            return;
        }

        // Extraction de la valeur kilométrique
        $newOdometer = $this->extractOdometerValue($expense);

        /**
         * Logique de mise à jour :
         * 1. On vérifie qu'une valeur a été trouvée.
         * 2. On s'assure que le nouvel odomètre est supérieur à l'actuel
         * (évite d'écraser avec une valeur erronée ou une ancienne facture).
         */
        if ($newOdometer && $newOdometer > (int) $vehicle->actual_odometer) {
            $vehicle->update([
                'actual_odometer' => (string) $newOdometer,
            ]);
        }
    }

    /**
     * Calculer le coût total d'un véhicule sur une période.
     */
    public function getTotalCost(Vehicle $vehicle, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = $vehicle->expenses();

        if ($startDate) {
            $query->where('expensed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('expensed_at', '<=', $endDate);
        }

        return (float) $query->sum('amount_total');
    }

    private function extractOdometerValue(Expense $expense): ?int
    {
        // Priorité 1 : Si nous décidons d'ajouter une colonne 'odometer' à la table 'expenses'
        if (isset($expense->odometer) && $expense->odometer > 0) {
            return (int) $expense->odometer;
        }

        return null;
    }
}
