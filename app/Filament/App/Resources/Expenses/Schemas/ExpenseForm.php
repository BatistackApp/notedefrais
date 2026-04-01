<?php

namespace App\Filament\App\Resources\Expenses\Schemas;

use App\Models\Category;
use App\Models\Vehicle;
use App\Services\OcrService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Justificatifs')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('receipt')
                            ->label('Ticket / Facture')
                            ->collection('receipts')
                            ->disk('public')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->live() // Indispensable pour déclencher afterStateUpdated immédiatement
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, Set $set, OcrService $ocrService) {
                                if (! $state) {
                                    return;
                                }

                                // Récupération du fichier temporaire (Filament stocke souvent un array ou un objet)
                                $file = is_array($state) ? array_values($state)[0] : $state;

                                if (! method_exists($file, 'getRealPath')) {
                                    return;
                                }

                                Notification::make()
                                    ->title('Analyse Gemini en cours...')
                                    ->info()
                                    ->send();

                                try {
                                    $data = $ocrService->analyzeReceipt($file->getRealPath());

                                    if (! empty($data)) {
                                        // Pré-remplissage des champs
                                        $set('title', $data['title'] ?? null);
                                        $set('amount_total', $data['amount_total'] ?? null);
                                        $set('tax_rate', $data['tax_rate'] ?? 20.00);
                                        $set('amount_taxe', $data['amount_taxe'] ?? null);
                                        $set('expensed_at', $data['expensed_at'] ?? now()->format('Y-m-d'));
                                        $set('vehicle_id', Vehicle::where('plaque', 'like', '%'.$data['vehicle_id'].'%')->first()->id ?? null);
                                        $set('odometer', $data['odometer'] ?? null);
                                        $set('category_id', Category::where('name', 'like', '%'.$data['category_id'].'%')->first()->id ?? null);

                                        Notification::make()
                                            ->title('Analyse terminée')
                                            ->body('Les informations du ticket ont été extraites.')
                                            ->success()
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    Log::error('Erreur OCR : '.$e->getMessage());
                                    Notification::make()
                                        ->title('Échec de l\'analyse')
                                        ->danger()
                                        ->send();
                                }
                            }),

                        Textarea::make('description')
                            ->label('Commentaire')
                            ->columnSpanFull(),
                    ]),

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
                            ->default(fn ($record) => \Auth::user()->id ?? '')
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
            ])->columns(1);
    }
}
