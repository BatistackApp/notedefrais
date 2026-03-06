<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Enums\ExpenseStatus;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use App\Services\ExpenseService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Editer la dépense'),
            Action::make('pending')
                ->label('Mise en attente')
                ->icon(Phosphor::Hourglass)
                ->color('info')
                ->hidden(fn (Expense $record) => $record->status !== ExpenseStatus::DRAFT)
                ->action(function (Expense $record, ExpenseService $service) {
                    $service->submit($record);
                    Notification::make()->title('Dépense en attente d\'approbation')->success()->send();
                }),
            Action::make('approve')
                ->label('Approuver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->hidden(fn (Expense $record) => $record->status !== ExpenseStatus::PENDING)
                ->action(function (Expense $record, ExpenseService $service) {
                    $service->approve($record);
                    Notification::make()->title('Dépense approuvée')->success()->send();
                }),
            Action::make('reject')
                ->label('Refuser')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('reason')
                        ->label('Motif du refus')
                        ->required(),
                ])
                ->hidden(fn (Expense $record) => $record->status !== ExpenseStatus::PENDING)
                ->action(function (Expense $record, array $data, ExpenseService $service) {
                    $service->reject($record, $data['reason']);
                    Notification::make()->title('Dépense refusée')->danger()->send();
                }),
        ];
    }
}
