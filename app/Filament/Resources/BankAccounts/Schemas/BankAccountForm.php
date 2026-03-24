<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nouveau compte bancaire')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du compte / Porteur')
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('iban')
                            ->label('IBAN')
                            ->maxLength('34'),

                        Select::make('currency')
                            ->label('Devise')
                            ->options(['EUR' => 'Euro (€)', 'USD' => 'US Dollar ($)'])
                            ->default('EUR')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Compte Actif')
                            ->default(true),
                    ]),
            ]);
    }
}
