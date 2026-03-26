<?php

namespace App\jobs;

use App\Enums\DigitalSealStatus;
use App\Models\Expense;
use App\Models\User;
use App\Services\DigitalSealService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyAllDigitalSealsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DigitalSealService $service): void
    {
        // Traitement par lot (chunk) pour ne pas exploser la RAM si l'entreprise a des milliers de tickets
        Expense::where('digital_seal_status', DigitalSealStatus::Sealed)
            ->chunk(100, function ($expenses) use ($service) {
                foreach ($expenses as $expense) {
                    $isValid = $service->verifyIntegrity($expense);

                    if (! $isValid) {
                        // Alerter tous les administrateurs comptables de cette anomalie grave
                        $admins = User::role('admin')->get();
                        foreach ($admins as $admin) {
                            $admin->notify(new DigitalSealCompromisedNotification($expense));
                        }
                    }
                }
            });
    }
}
