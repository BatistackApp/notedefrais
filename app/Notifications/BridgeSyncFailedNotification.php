<?php

namespace App\Notifications;

use App\Models\BankAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BridgeSyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BankAccount $account) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('⚠️ Action requise : Reconnexion bancaire nécessaire')
            ->line("La synchronisation de votre compte bancaire '{$this->account->name}' a été interrompue.")
            ->line('Conformément à la directive européenne DSP2, vous devez renouveler votre consentement bancaire pour des raisons de sécurité.')
            ->action('Reconnecter ma banque', url('/admin/bank-accounts')) // Lien vers le panel Filament
            ->line('Sans action de votre part, les nouvelles dépenses ne pourront plus être rapprochées automatiquement.');
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->danger()
            ->title('⚠️ Action requise : Reconnexion bancaire nécessaire')
            ->body('Conformément à la directive européenne DSP2, vous devez renouveler votre consentement bancaire pour des raisons de sécurité.')
            ->getDatabaseMessage();
    }
}
