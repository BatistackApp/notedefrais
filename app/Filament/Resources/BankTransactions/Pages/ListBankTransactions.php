<?php

namespace App\Filament\Resources\BankTransactions\Pages;

use App\Filament\Resources\BankTransactions\BankTransactionResource;
use App\Models\BankAccount;
use App\Services\BankTransactionImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListBankTransactions extends ListRecords
{
    protected static string $resource = BankTransactionResource::class;

    protected static ?string $title = 'Liste des Transactions';

    protected static ?string $breadcrumb = 'Liste des transactions';

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            Action::make('import-csv')
                ->label('Importer un relevé (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->schema([
                    Select::make('bank_account_id')
                        ->label('Compte bancaire cible')
                        ->options(BankAccount::where('is_active')->pluck('name', 'id'))
                        ->required(),

                    FileUpload::make('csv_file')
                        ->label('Fichier CSV')
                        ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                        ->required(),
                ])
                ->action(function (array $data, BankTransactionImportService $service) {
                    $bankAccount = BankAccount::find($data['bank_account_id']);
                    // Le composant FileUpload sauvegarde le fichier temporairement. On récupère le chemin absolu.
                    $filePath = Storage::disk('public')->path($data['csv_file']);

                    $csvData = [];
                    // Lecture basique d'un CSV (Peut être remplacé par la librairie League/Csv si besoin)
                    if (($handle = fopen($filePath, 'r')) !== false) {
                        $header = fgetcsv($handle, 1000, ',');
                        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                            if (count($header) === count($row)) {
                                $csvData[] = array_combine($header, $row);
                            }
                        }
                        fclose($handle);
                    }

                    // Envoi au service métier défini à l'Étape 4
                    $count = $service->importFromCsv($bankAccount, $csvData);

                    // Nettoyage : On supprime le fichier CSV temporaire
                    Storage::disk('public')->delete($data['csv_file']);

                    Notification::make()
                        ->title('Import réussi')
                        ->body("$count nouvelles transactions importées.")
                        ->success()
                        ->send();

                }),

            Action::make('syncApiBridge')
                ->label('Synchroniser avec la Banque')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    // Ici, on déclencherait un Job qui irait appeler l'API Bridge (ou GoCardless).
                    // Exemple : SyncBridgeBankAccountsJob::dispatch();

                    Notification::make()
                        ->title('Synchronisation lancée')
                        ->body('La récupération depuis l\'API Open Banking a démarré en arrière-plan.')
                        ->info()
                        ->send();
                }),
        ];
    }
}
