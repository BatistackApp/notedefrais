<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use DB;
use Illuminate\Support\Collection;

/**
 * Service gérant les calculs statistiques et KPI du tableau de bord.
 */
class AnalyticsService
{
    /**
     * Récupère le top des chantiers les plus coûteux.
     */
    public function getTopSitesByCost(int $limit = 5): Collection
    {
        return Expense::query()
            ->select('site_reference', DB::raw('SUM(amount_total) as total_cost'))
            ->where('status', ExpenseStatus::APPROVED)
            ->whereNotNull('site_reference')
            ->groupBy('site_reference')
            ->orderByDesc('total_cost')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les dépenses mensuelles globales sur l'année en cours.
     */
    public function getMonthlyEvolution(): Collection
    {
        return Expense::query()
            ->select(
                DB::raw('MONTH(expensed_at) as month'),
                DB::raw('SUM(amount_total) as total')
            )
            ->where('status', ExpenseStatus::APPROVED)
            ->whereYear('expensed_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Calcule le montant total des dépenses "en attente" (flux de trésorerie engagé).
     */
    public function getPendingCommitment(): float
    {
        return (float) Expense::where('status', ExpenseStatus::PENDING)->sum('amount_total');
    }
}
