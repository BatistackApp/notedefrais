<?php

namespace App\Filament\App\Resources\Expenses\Pages;

use App\Enums\ExpenseStatus;
use App\Filament\App\Resources\Expenses\ExpenseResource;
use App\Services\ExpenseService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;
    protected static ?string $title = 'Fiche d\'une Dépense';
    protected static ?string $breadcrumb = 'Fiche d\'une Dépense';

    /**
     * Actions disponibles dans l'en-tête de la visualisation.
     */
    protected function getHeaderActions(): array
    {
        return [
            // Permet de retourner à l'édition si le frais est encore modifiable
            EditAction::make()
                ->visible(fn ($record) => in_array($record->status, [
                    ExpenseStatus::DRAFT,
                    ExpenseStatus::REJECTED,
                ])),

            // Action de soumission officielle à la comptabilité
            Action::make('submit')
                ->label('Envoyer à la compta')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->modalHeading('Soumettre la note de frais')
                ->modalDescription('Une fois envoyée, vous ne pourrez plus modifier cette dépense sauf si elle est rejetée par l\'administration.')
                ->visible(fn ($record) => $record->status === ExpenseStatus::DRAFT)
                ->action(function ($record, ExpenseService $service) {
                    try {
                        $service->submit($record);

                        Notification::make()
                            ->title('Dépense envoyée')
                            ->body('Votre justificatif est maintenant en attente de validation.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Action impossible')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Action de suppression uniquement pour les brouillons
            DeleteAction::make()
                ->visible(fn ($record) => $record->status === ExpenseStatus::DRAFT),
        ];
    }
}
