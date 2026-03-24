<?php

namespace App\Filament\Resources\BankAccounts\Pages;

use App\Filament\Resources\BankAccounts\BankAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;
    protected static ?string $title = 'Liste des comptes bancaires';
    protected static ?string $breadcrumb = 'Liste des comptes bancaires';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create')
                ->label('Ajouter un compte bancaire')
                ->icon(Phosphor::PlusCircle),
        ];
    }
}
