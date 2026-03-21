<?php

namespace App\Filament\App\Resources\Expenses\Pages;

use App\Enums\ExpenseStatus;
use App\Filament\App\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected static ?string $title = 'Edition d\'une dépense';

    protected static ?string $breadcrumb = 'Edition d\'une dépense';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (Expense $record): bool => $record->status === ExpenseStatus::DRAFT),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
