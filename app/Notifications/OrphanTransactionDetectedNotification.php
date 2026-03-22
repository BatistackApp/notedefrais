<?php

namespace App\Notifications;

use App\Models\BankTransaction;
use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrphanTransactionDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BankTransaction $transaction) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action requise : Justificatif manquant')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Nous avons détecté une transaction par carte d\'entreprise sans justificatif associé.')
            ->line('Détails du paiement :')
            ->line('- Date : '.$this->transaction->transaction_date->format('d/m/Y'))
            ->line('- Marchand : '.$this->transaction->vendor_name)
            ->line('- Montant : '.number_format(abs($this->transaction->amount), 2).' '.$this->transaction->currency)
            ->action('Soumettre mon justificatif', url('/app/expenses/create'))
            ->line('Merci de régulariser cette situation au plus vite pour notre comptabilité.');
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->warning()
            ->title('Action requise : Justificatif manquant')
            ->body('Justificatif manquant pour '.$this->transaction->vendor_name.' ('.abs($this->transaction->amount).'€)')
            ->actions([
                Action::make()
                    ->label('Soumettre mon justificatif')
                    ->url('/app/expenses/create'),
            ])
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'message' => 'Justificatif manquant pour '.$this->transaction->vendor_name.' ('.abs($this->transaction->amount).'€)',
        ];
    }
}
