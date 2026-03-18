<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Models\Vehicle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plaque')
                    ->label('Plaque Immatriculation')
                    ->searchable(),

                TextColumn::make('brand')
                    ->label('Marque/Modele')
                    ->formatStateUsing(function (Vehicle $record) {
                        return new HtmlString("
                        <strong>Marque:</strong> {$record->brand}<br>
                        <strong>Modèle:</strong> {$record->model}<br>
                        ");
                    }),

                TextColumn::make('actual_odometer')
                    ->label('Odomètre actuel')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
