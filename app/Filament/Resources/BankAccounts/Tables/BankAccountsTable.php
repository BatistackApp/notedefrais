<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                TextColumn::make('transactions_count')
                    ->counts('transactions')
                    ->label('Nb Transactions'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status du compte'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
