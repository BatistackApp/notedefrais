<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Expenses\Widgets\ExpenseStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;
    protected static ?string $title = 'Liste des dépenses';
    protected static ?string $breadcrumb = 'Liste des dépenses';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle dépense')->icon(Phosphor::PlusCircle),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseStatsOverview::class,
        ];
    }
}
