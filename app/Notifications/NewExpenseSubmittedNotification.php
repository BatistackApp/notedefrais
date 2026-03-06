<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Notifications\Notification;

class NewExpenseSubmittedNotification extends Notification
{
    public function __construct(public Expense $expense) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->info()
            ->title("Nouvelle dépense de {$this->expense->user->name} à valider.")
            ->getDatabaseMessage();
    }
}
