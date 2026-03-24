<?php

namespace App\Filament\Resources\BankAccounts\Pages;

use App\Filament\Resources\BankAccounts\BankAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class EditBankAccount extends EditRecord
{
    protected static string $resource = BankAccountResource::class;
    protected static ?string $title = 'Editer compte bancaire';
    protected static ?string $breadcrumb = 'Editer compte bancaire';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Supprimer')->icon(Phosphor::Trash),
        ];
    }
}
