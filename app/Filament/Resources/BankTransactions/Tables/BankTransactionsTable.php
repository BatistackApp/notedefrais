<?php

namespace App\Filament\Resources\BankTransactions\Tables;

use App\Enums\ReconciliationStatus;
use App\Models\BankTransaction;
use App\Models\Expense;
use App\Services\BankReconciliationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BankTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Aucune transactions bancaires enregistrées')
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('bankAccount.name')
                    ->label('Compte')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('vendor_name')
                    ->label('Marchand')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                TextColumn::make('reconciliation_status')
                    ->label('Statut')
                    ->badge()
                    ->tooltip(fn ($state) => $state->getDescription()),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                SelectFilter::make('reconciliation_status')
                    ->label('Statut de rapprochement')
                    ->options(ReconciliationStatus::class)
                    ->default(ReconciliationStatus::Pending->value),
            ])
            ->recordActions([
                Action::make('reconcile')
                    ->label('Rapprocher')
                    ->icon(Phosphor::Link)
                    ->color('success')
                    ->visible(fn (BankTransaction $record) => $record->reconciliation_status === ReconciliationStatus::Pending)
                    ->schema([
                        Select::make('expense_id')
                            ->label('Sélectionner la note de frais à rapprocher')
                            ->options(function (BankTransaction $record) {
                                // On suggère les notes de frais en attente avec un montant similaire (marge de 10%)
                                $targetAmount = abs($record->amount);
                                $minAmount = $targetAmount * 0.9;
                                $maxAmount = $targetAmount * 1.1;

                                return Expense::where('reconciliation_status', ReconciliationStatus::Pending->value)
                                    ->whereBetween('amount_total', [$minAmount, $maxAmount])
                                    ->get()
                                    ->mapWithKeys(function ($expense) {
                                        return [$expense->id => "{$expense->expensed_at->format('d/m/Y')} - {$expense->amount_total}€ - Ticket #{$expense->id} ({$expense->title})"];
                                    });
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (BankTransaction $record, array $data, BankReconciliationService $service) {
                        $expense = Expense::find($data['expense_id']);
                        $service->reconcile($record, $expense, ReconciliationStatus::ManualReconciled);
                    })
                    ->successNotificationTitle('Rapprochement effectué avec succès !'),

                Action::make('ignore')
                    ->label('Ignorer')
                    ->icon(Phosphor::EyeSlash)
                    ->color('gray')
                    ->visible(fn (BankTransaction $record) => $record->reconciliation_status === ReconciliationStatus::Pending)
                    ->action(fn (BankTransaction $record) => $record->update(['reconciliation_status' => ReconciliationStatus::Ignored->value])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
