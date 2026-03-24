<?php

namespace App\Filament\Resources\BankAccounts\Pages;

use App\Filament\Resources\BankAccounts\BankAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankAccount extends CreateRecord
{
    protected static string $resource = BankAccountResource::class;
    protected static ?string $title = 'Nouveau compte bancaire';
    protected static ?string $breadcrumb = 'Nouveau compte bancaire';
}
