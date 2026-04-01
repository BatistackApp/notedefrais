<?php

namespace App\Filament\Resources\BankAccounts\Pages;

use App\Filament\Resources\BankAccounts\BankAccountResource;
use Filament\Actions\Action;
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

            Action::make('connect_bridge')
                ->label('Connecter ma Banque (Bridge)')
                ->icon('heroicon-o-link')
                ->color('primary')
                ->url(route('bridge.connect')) // Appel de la route web créée à l'Étape 7
                ->openUrlInNewTab(true),
        ];
    }
}
