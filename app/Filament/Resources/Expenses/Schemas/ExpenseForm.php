<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),

                        Select::make('user_id')
                            ->label('Salarié')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('category_id')
                            ->label('Categorie')
                            ->relationship('category', 'name')
                            ->required(),

                        Select::make('vehicle_id')
                            ->label('Vehicule')
                            ->relationship('vehicle', 'plaque')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('odometer', null))
                            ->placeholder('Aucun vehicule'),

                        TextInput::make('odometer')
                            ->label('Kilométrage actuel')
                            ->numeric()
                            ->suffix('km')
                            ->required(fn (Get $get) => filled($get('vehicle_id'))) // Requis si un véhicule est choisi
                            ->visible(fn (Get $get) => filled($get('vehicle_id'))) // Visible seulement si un véhicule est choisi
                            ->helperText('Saisissez le kilométrage affiché au compteur.'),

                        DateTimePicker::make('expensed_at')
                            ->label('Date du frais')
                            ->required(),

                        TextInput::make('site_reference')
                            ->label('Référence Chantier')
                            ->placeholder('VALASTRO'),

                    ])->columns(2),

                Section::make('Détails Financiers')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('amount_total')
                            ->label('Montant TTC')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('tax_rate')
                            ->label('Taux TVA (%)')
                            ->numeric()
                            ->default(20)
                            ->required(),

                        TextInput::make('amount_taxe')
                            ->label('Montant TVA')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])->columns(3),

                Section::make('Justificatifs')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('receipt')
                            ->label('Ticket / Facture')
                            ->collection('receipts')
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->openable(),

                        Textarea::make('description')
                            ->label('Commentaire')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
