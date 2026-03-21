<?php

namespace App\Filament\App\Resources\Expenses\Pages;

use App\Filament\App\Resources\Expenses\ExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;
    protected static ?string $title = 'Liste de mes dépenses';
    protected static ?string $breadcrumb = 'Liste de mes dépenses';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle dépense')
                ->icon(Phosphor::PlusCircle),
        ];
    }
}
