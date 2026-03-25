<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'md' => 3])
                    ->columnSpanFull()
                    ->schema([
                        // Colonne de gauche : Détails
                        Group::make([
                            Section::make('Informations générales')
                                ->schema([
                                    TextEntry::make('title')
                                        ->label('Titre / Marchand')
                                        ->weight('bold'),
                                    TextEntry::make('category.name')
                                        ->label('Catégorie')
                                        ->icon(fn ($record) => $record->category->icon ?? 'heroicon-o-tag'),
                                    TextEntry::make('expensed_at')
                                        ->label('Date de la dépense')
                                        ->date('d F Y'),
                                    TextEntry::make('site_reference')
                                        ->label('Référence Chantier')
                                        ->placeholder('Aucune référence')
                                        ->badge()
                                        ->color('gray'),
                                ])->columns(2),

                            Section::make('Détails financiers')
                                ->schema([
                                    TextEntry::make('amount_total')
                                        ->label('Montant TTC')
                                        ->money('EUR')
                                        ->size('lg')
                                        ->weight('bold')
                                        ->color('primary'),
                                    TextEntry::make('tax_rate')
                                        ->label('Taux TVA')
                                        ->suffix('%'),
                                    TextEntry::make('amount_taxe')
                                        ->label('Montant TVA')
                                        ->money('EUR'),
                                ])->columns(3),

                            Section::make('Véhicule & Trajet')
                                ->schema([
                                    TextEntry::make('vehicle.plaque')
                                        ->label('Véhicule utilisé')
                                        ->placeholder('Non lié à un véhicule')
                                        ->badge(),
                                    TextEntry::make('odometer')
                                        ->label('Kilométrage au compteur')
                                        ->suffix(' km')
                                        ->visible(fn ($record) => filled($record->vehicle_id)),
                                ])
                                ->visible(fn ($record) => filled($record->vehicle_id))
                                ->columns(2),
                        ])->columnSpan(['md' => 2]),

                        // Colonne de droite : Statut et Justificatif
                        Group::make([
                            Section::make('Statut de la demande')
                                ->schema([
                                    TextEntry::make('status')
                                        ->label('État actuel')
                                        ->badge(),
                                    TextEntry::make('description')
                                        ->label('Notes / Motif de refus')
                                        ->markdown()
                                        ->visible(fn ($record) => filled($record->description)),
                                ]),

                            Section::make('Justificatif numérisé')
                                ->schema([
                                    SpatieMediaLibraryImageEntry::make('receipt')
                                        ->label('')
                                        ->collection('receipts')
                                        ->extraImgAttributes([
                                            'class' => 'rounded-lg shadow-sm border w-full h-auto',
                                            'alt' => 'Justificatif de dépense',
                                        ]),
                                ]),


                        ])->columnSpan(['md' => 1]),
                    ]),
            ]);
    }
}
