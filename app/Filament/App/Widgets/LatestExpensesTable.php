<?php

namespace App\Filament\App\Widgets;

use App\Models\Expense;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestExpensesTable extends TableWidget
{
    protected static ?string $heading = 'Mes dernières dépenses';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Expense::query()
                ->where('user_id', Auth::id())
                ->latest('expensed_at')
                ->limit(5))
            ->columns([
                TextColumn::make('expensed_at')
                    ->label('Date')
                    ->date('d/m/Y'),
                TextColumn::make('title')
                    ->label('Objet')
                    ->limit(20),
                TextColumn::make('amount_total')
                    ->label('Montant')
                    ->money('EUR'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
