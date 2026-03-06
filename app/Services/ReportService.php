<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

/**
 * Service gérant les exports (PDF/Excel) pour la comptabilité.
 */
class ReportService
{
    /**
     * Génère un PDF récapitulatif mensuel par chantier via Browsershot.
     */
    public function generateMonthlySiteReport(string $siteReference, int $month, int $year): string
    {
        $expenses = Expense::where('site_reference', $siteReference)
            ->whereMonth('expensed_at', $month)
            ->whereYear('expensed_at', $year)
            ->where('status', \App\Enums\ExpenseStatus::APPROVED)
            ->get();

        $html = View::make('reports.site-expenses', compact('expenses', 'siteReference'))->render();

        $fileName = "report_{$siteReference}_{$month}_{$year}.pdf";
        $path = storage_path("app/public/reports/{$fileName}");

        Browsershot::html($html)
            ->setNodeBinary(config('services.browsershot.node_path'))
            ->setNpmBinary(config('services.browsershot.npm_path'))
            ->showBackground()
            ->margins(10, 10, 10, 10)
            ->save($path);

        return $path;
    }
}
