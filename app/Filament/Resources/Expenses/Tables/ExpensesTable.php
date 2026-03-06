<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Services\ExpenseService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Aucune note de frais disponible')
            ->emptyStateIcon(Phosphor::ReceiptX)
            ->searchPlaceholder('Rechercher...')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Salarié')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Dépense')
                    ->description(fn (Expense $record): string => $record->category->name)
                    ->searchable(),

                TextColumn::make('expensed_at')
                    ->label('Date')
                    ->date('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('amount_total')
                    ->label('Montant TTC')
                    ->money('EUR')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                TextColumn::make('site_reference')
                    ->label('Chantier')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ExpenseStatus::class),

                SelectFilter::make('category')
                    ->label('Categorie')
                    ->relationship('category', 'name'),

                Filter::make('expensed_at')
                    ->schema([
                        DatePicker::make('from')->label('Depuis'),
                        DatePicker::make('until')->label('Jusqu\'à'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('expensed_at', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('expensed_at', '<=', $date))),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('approve')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->hidden(fn (Expense $record) => $record->status !== ExpenseStatus::PENDING)
                        ->action(function (Expense $record, ExpenseService $service) {
                            $service->approve($record);
                            Notification::make()->title('Dépense approuvée')->success()->send();
                        }),
                    Action::make('reject')
                        ->label('Refuser')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('reason')
                                ->label('Motif du refus')
                                ->required(),
                        ])
                        ->hidden(fn (Expense $record) => $record->status !== ExpenseStatus::PENDING)
                        ->action(function (Expense $record, array $data, ExpenseService $service) {
                            $service->reject($record, $data['reason']);
                            Notification::make()->title('Dépense refusée')->danger()->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
