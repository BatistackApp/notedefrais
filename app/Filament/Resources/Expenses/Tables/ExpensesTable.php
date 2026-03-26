<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Enums\DigitalSealStatus;
use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Services\DigitalSealService;
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
use Filament\Tables\Columns\IconColumn;
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

                IconColumn::make('reconciliation_status')
                    ->label('Banque')
                    ->icon(fn ($state) => $state->getIcon())
                    ->color(fn ($state) => $state->getColor())
                    ->tooltip(fn ($state): string => $state->getDescription()),

                IconColumn::make('digital_seal_status')
                    ->label('Sceau Légal')
                    ->icon(fn (DigitalSealStatus $state): string => $state->getIcon())
                    ->color(fn ($state) => $state->getColor())
                    ->tooltip(fn ($state): string => $state->getDescription()),
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

                SelectFilter::make('digital_seal_status')
                    ->label('Intégrité du document')
                    ->options(DigitalSealStatus::class),
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

                    Action::make('verify_integrity')
                        ->label('Auditer')
                        ->icon(Phosphor::Fingerprint)
                        ->color('gray')
                        ->visible(fn ($record) => $record->digital_seal_status === DigitalSealStatus::Sealed)
                        ->action(function ($record, DigitalSealService $service) {
                            $isValid = $service->verifyIntegrity($record);

                            if ($isValid) {
                                Notification::make()
                                    ->title('Audit réussi')
                                    ->body('L\'empreinte SHA-256 correspond au fichier original. L\'intégrité est garantie.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Intégrité Compromise !')
                                    ->body('Le fichier actuel a été modifié ou corrompu. L\'empreinte ne correspond plus.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('verify_integrity')
                        ->label('Auditer')
                        ->icon(Phosphor::Fingerprint)
                        ->color('gray')
                        ->visible(fn ($record) => empty($record->digital_seal_status) || $record->digital_seal_status === DigitalSealStatus::Sealed)
                        ->action(function ($record, DigitalSealService $service) {
                            $isValid = $service->verifyIntegrity($record);

                            if ($isValid) {
                                Notification::make()
                                    ->title('Audit réussi')
                                    ->body('L\'empreinte SHA-256 correspond au fichier original. L\'intégrité est garantie.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Intégrité Compromise !')
                                    ->body('Le fichier actuel a été modifié ou corrompu. L\'empreinte ne correspond plus.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
