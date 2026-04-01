<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use App\jobs\SyncBridgeTransactionsJob;
use App\Models\BankAccount;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Aucun comptes bancaires actuellement')
            ->emptyStateIcon(Phosphor::CreditCard)
            ->emptyStateActions([
                CreateAction::make('create')
                    ->label('Ajouter un compte bancaire')
                    ->icon(Phosphor::PlusCircle),
            ])
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('iban')->label('Iban')->searchable(),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                IconColumn::make('bridge_account_id')
                    ->label('Connecté (Bridge)')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->tooltip(fn ($state) => $state ? 'Synchronisation automatique activée' : 'Compte manuel'),
                TextColumn::make('last_synced_at')
                    ->label('Dernière Synchro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Nb Transactions'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status du compte'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('sync_now')
                    ->label('Synchroniser')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (BankAccount $record) => $record->bridge_account_id !== null) // Visible uniquement si connecté à Bridge
                    ->action(function (BankAccount $record) {
                        // On lance le Job de synchronisation spécifiquement pour ce compte (ou globalement)
                        SyncBridgeTransactionsJob::dispatch();

                        Notification::make()
                            ->title('Synchronisation lancée')
                            ->body('La récupération des transactions est en cours d\'exécution en arrière-plan.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
