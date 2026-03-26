<?php

namespace App\Filament\Resources\Expenses\Widgets;

use App\Enums\DigitalSealStatus;
use App\Enums\ExpenseStatus;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ExpenseStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Calcul du montant global approuvé sur le mois en cours
        $monthlyApprovedAmount = Expense::query()
            ->where('status', ExpenseStatus::APPROVED)
            ->whereMonth('expensed_at', now()->month)
            ->whereYear('expensed_at', now()->year)
            ->sum('amount_total');

        $compromisedCount = Expense::where('digital_seal_status', DigitalSealStatus::Compromised)->count();

        return [
            Stat::make('Total des dépenses', Expense::count())
                ->description('Toutes dépenses confondues')
                ->icon('heroicon-m-rectangle-stack'),

            Stat::make('En attente (Pending)', Expense::where('status', ExpenseStatus::PENDING)->count())
                ->description('À valider par la compta')
                ->color('warning')
                ->icon('heroicon-m-clock'),

            Stat::make('Validées (Approved)', Expense::where('status', ExpenseStatus::APPROVED)->count())
                ->description('Justificatifs conformes')
                ->color('success')
                ->icon('heroicon-m-check-circle'),

            Stat::make('Refusées (Rejected)', Expense::where('status', ExpenseStatus::REJECTED)->count())
                ->description('Nécessitent une correction')
                ->color('danger')
                ->icon('heroicon-m-x-circle'),

            Stat::make('Total Approuvé (Mois)', Number::currency($monthlyApprovedAmount, 'EUR'))
                ->description('Dépenses validées ce mois-ci')
                ->color('info')
                ->icon('heroicon-m-banknotes'),

            Stat::make('Alerte de Sécurité Majeure', $compromisedCount.' justificatif(s) compromis')
                ->description('Des fichiers ont été altérés. La valeur probatoire est perdue.')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'ring-2 ring-danger-500 bg-danger-50', // Visuel agressif pour attirer l'attention
                ]),
        ];
    }
}
