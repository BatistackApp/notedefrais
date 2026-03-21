<?php

namespace App\Filament\App\Resources\Expenses;

use App\Filament\App\Resources\Expenses\Pages\CreateExpense;
use App\Filament\App\Resources\Expenses\Pages\EditExpense;
use App\Filament\App\Resources\Expenses\Pages\ListExpenses;
use App\Filament\App\Resources\Expenses\Pages\ViewExpense;
use App\Filament\App\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\App\Resources\Expenses\Schemas\ExpenseInfolist;
use App\Filament\App\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Expense;
use Auth;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Camera;
    protected static ?string $navigationLabel = 'Mes Dépenses';

    public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpenseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'view' => ViewExpense::route('/{record}'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
