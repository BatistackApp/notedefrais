<?php

namespace App\Filament\App\Widgets;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        // Total approuvé ce mois-ci
        $monthlyTotal = Expense::query()
            ->where('user_id', $userId)
            ->where('status', ExpenseStatus::APPROVED)
            ->whereMonth('expensed_at', now()->month)
            ->whereYear('expensed_at', now()->year)
            ->sum('amount_total');

        // Nombre de tickets en attente
        $pendingCount = Expense::query()
            ->where('user_id', $userId)
            ->where('status', ExpenseStatus::PENDING)
            ->count();

        // Catégorie la plus utilisée ce mois-ci
        $topCategory = Expense::query()
            ->where('user_id', $userId)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(amount_total) as total'))
            ->whereMonth('expensed_at', now()->month)
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->first();

        return [
            Stat::make('Mon Total (Mois)', Number::currency($monthlyTotal, 'EUR'))
                ->description('Dépenses validées ce mois-ci')
                ->color('success')
                ->icon('heroicon-m-banknotes'),

            Stat::make('En attente', $pendingCount)
                ->description('Tickets à valider par la compta')
                ->color('warning')
                ->icon('heroicon-m-clock'),

            Stat::make('Top Catégorie', $topCategory?->name ?? 'N/A')
                ->description($topCategory ? Number::currency($topCategory->total, 'EUR').' dépensés' : 'Aucune dépense')
                ->color('info')
                ->icon('heroicon-m-tag'),
        ];
    }
}
