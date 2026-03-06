<?php

namespace App\Console\Commands;

use App\Enums\ExpenseStatus;
use App\Mails\WeeklySummaryMail;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WeeklySummaryCommand extends Command
{
    protected $signature = 'app:weekly-summary';

    protected $description = 'Envoie une synthèse des dépenses de la semaine par email aux administrateurs.';

    public function handle(): int
    {
        $this->info('Compilation de la synthèse hebdomadaire...');

        // Récupération des dépenses de la semaine glissante
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $expenses = Expense::with(['category', 'user'])
            ->whereBetween('expensed_at', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', ExpenseStatus::DRAFT)
            ->get();

        if ($expenses->isEmpty()) {
            $this->info('Aucune dépense à signaler cette semaine.');

            return self::SUCCESS;
        }

        // Statistiques rapides
        $data = [
            'total_amount' => $expenses->sum('amount_total'),
            'count' => $expenses->count(),
            'by_category' => $expenses->groupBy('category.name')->map->sum('amount_total'),
            'by_site' => $expenses->groupBy('site_reference')->map->sum('amount_total'),
            'pending_count' => $expenses->where('status', ExpenseStatus::PENDING)->count(),
        ];

        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $this->error("Aucun administrateur trouvé pour recevoir l'email.");
            return self::FAILURE;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new WeeklySummaryMail($data, $startOfWeek, $endOfWeek));
        }

        $this->info("Synthèse envoyée à {$admins->count()} administrateurs.");
        return self::SUCCESS;
    }
}
