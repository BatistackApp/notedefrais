<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information sur l\'utilisateur')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom/Prénom')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->hint('Adresse Mail valide requise, le mot de passe provisoir est envoyer à cette adresse uniquement.'),


                        Select::make('roles')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->label('Roles')
                            ->required(),

                    ]),
            ]);
    }
}
