<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Collection;

/**
 * Service gérant la logique liée aux chantiers (références sites).
 */
class SiteService
{
    /**
     * Liste toutes les références de chantiers actives (ayant eu des frais récemment).
     */
    public function getActiveSiteReferences(): Collection
    {
        return Expense::query()
            ->distinct()
            ->whereNotNull('site_reference')
            ->where('expensed_at', '>=', now()->subMonths(6))
            ->pluck('site_reference');
    }

    /**
     * Récupère l'historique complet des dépenses pour un chantier spécifique.
     */
    public function getSiteHistory(string $siteReference): Collection
    {
        return Expense::with(['user', 'category'])
            ->where('site_reference', $siteReference)
            ->orderByDesc('expensed_at')
            ->get();
    }
}
