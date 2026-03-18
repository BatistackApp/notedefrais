<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information du véhicule')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('brand')
                            ->label('Marque')
                            ->required(),

                        TextInput::make('model')
                            ->label('Model')
                            ->required(),

                        TextInput::make('plaque')
                            ->label('Plaque Immatriculation')
                            ->mask('aa-999-aa')
                            ->required(),

                        TextInput::make('actual_odometer')
                            ->label('Actual Odometer')
                            ->integer()
                            ->numeric(),
                    ])->columns(2),
            ]);
    }
}
