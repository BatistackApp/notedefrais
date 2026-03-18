<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;
    protected static ?string $title = 'Liste des immatriculations';
    protected static ?string $breadcrumb = 'Liste des immatriculations';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Nouveau véhicule')
            ->icon(Phosphor::PlusCircle),
        ];
    }
}
