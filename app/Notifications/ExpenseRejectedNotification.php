<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class ExpenseRejectedNotification extends Notification
{
    public function __construct(
        public Expense $expense,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Justificatif de dépense refusé')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le justificatif pour votre dépense \"{$this->expense->title}\" du {$this->expense->expensed_at->format('d/m/Y')} a été refusé.")
            ->line("**Motif du refus :** {$this->reason}")
            ->action('Modifier ma dépense', url('/app/expenses/'.$this->expense->id.'/edit'))
            ->line('Merci de fournir un nouveau justificatif conforme dès que possible.');
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('Justificatif de dépense refusé')
            ->body("Le justificatif pour votre dépense \"{$this->expense->title}\" du {$this->expense->expensed_at->format('d/m/Y')} a été refusé.")
            ->danger()
            ->getDatabaseMessage();
    }
}
