<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use App\Services\SiteService;
use Illuminate\Console\Command;

class GenerateReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports {--month=} {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les rapports PDF par chantier pour un mois spécifique.';

    /**
     * Execute the console command.
     */
    public function handle(SiteService $siteService, ReportService $reportService): int
    {
        $month = $this->option('month') ?? now()->subMonth()->month;
        $year = $this->option('year') ?? now()->subMonth()->year;

        $sites = $siteService->getActiveSiteReferences();

        if ($sites->isEmpty()) {
            $this->info('Aucun chantier actif trouvé pour cette période.');

            return self::SUCCESS;
        }

        $this->info("Génération des rapports pour {$sites->count()} chantiers...");

        foreach ($sites as $siteRef) {
            $this->info("Traitement du chantier : {$siteRef}");
            try {
                $reportService->generateMonthlySiteReport($siteRef, (int) $month, (int) $year);
            } catch (\Exception $e) {
                $this->error("Erreur pour {$siteRef} : ".$e->getMessage());
            }
        }

        $this->info('Opération terminée.');

        return self::SUCCESS;
    }
}
