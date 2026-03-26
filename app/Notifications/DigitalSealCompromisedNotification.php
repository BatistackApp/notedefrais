<?php

namespace App\Notifications;

use App\Models\Expense;
use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DigitalSealCompromisedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Expense $expense) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('🚨 URGENT : Intégrité d\'un justificatif compromise')
            ->line('Le système d\'audit nocturne a détecté une anomalie d\'intégrité (Hash SHA-256 invalide) sur une note de frais.')
            ->line('Note de Frais # : '.$this->expense->id)
            ->line('Employé : '.$this->expense->user->name)
            ->line('Le fichier a pu être altéré manuellement ou corrompu sur le disque d\'hébergement. La valeur probatoire de ce document est annulée.')
            ->action('Voir la note de frais', url('/admin/expenses/'.$this->expense->id))
            ->line('Veuillez contacter votre administrateur système immédiatement.');
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->danger()
            ->title('🚨 URGENT : Intégrité d\'un justificatif compromise')
            ->body('Le système d\'audit nocturne a détecté une anomalie d\'intégrité (Hash SHA-256 invalide) sur une note de frais.')
            ->actions([
                Action::make('view')
                    ->label('Voir la note de frais')
                    ->url(url('/admin/expenses/'.$this->expense->id)),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return [
            'expense_id' => $this->expense->id,
            'message' => 'L\'intégrité du fichier de la dépense #'.$this->expense->id.' est compromise !',
        ];
    }
}
