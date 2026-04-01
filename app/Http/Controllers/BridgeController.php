<?php

namespace App\Http\Controllers;

use App\jobs\FetchBridgeAccountDetailsJob;
use App\jobs\SyncBridgeTransactionsJob;
use App\Models\BankAccount;
use App\Services\BridgeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BridgeController extends Controller
{
    public function connect(BridgeApiService $bridgeService)
    {
        try {
            $url = $bridgeService->createConnectLink(auth()->user());
            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error('Erreur Bridge Connect : ' . $e->getMessage());
            return redirect(route('filament.admin.resources.bank-accounts.index'))
                ->with('error', 'Impossible de contacter le service bancaire.');
        }
    }

    /**
     * @throws \Exception
     */
    public function callback(Request $request, BridgeApiService $bridgeService)
    {
        $status = $request->query('success');
        $itemId = $request->query('item_id'); // L'ID de la connexion créée

        // Si l'utilisateur a annulé ou si Bridge a échoué
        if ($status !== 'true' || !$itemId) {
            Log::warning('Connexion Bridge annulée ou échouée', $request->all());
            return redirect(route('filament.admin.resources.bank-accounts.index'))
                ->with('error', 'La connexion bancaire a été annulée ou a échoué.');
        }

        // Création ou mise à jour du compte bancaire en base.
        // ATTENTION : L'enregistrement du bridge_item_id va déclencher notre BankAccountObserver (Étape 5).
        // L'Observer lancera les Jobs asynchrones pour récupérer le vrai nom du compte et les transactions.
        $accounts = $bridgeService->getAccountDetailsFromItemId($itemId);
        foreach ($accounts['resources'] as $resource) {
            $bankAccount = BankAccount::updateOrCreate(
                ['bridge_item_id' => $itemId],
                [
                    'name' => 'Compte en cours de synchronisation...', // Sera mis à jour par le Job FetchBridgeAccountDetailsJob
                    'is_active' => !($resource['data_access'] === 'disabled'),
                    'bridge_account_id' => $resource['id'],
                    'iban' => $resource['iban'] ?? null,
                ]
            );

            FetchBridgeAccountDetailsJob::dispatch($bankAccount);
        }

        // 2. On lance une première synchronisation immédiate des transactions
        SyncBridgeTransactionsJob::dispatch();

        // Redirection vers le dashboard Filament.
        // L'Observer s'occupera d'afficher la notification Filament de succès.
        return redirect(route('filament.admin.resources.bank-accounts.index'));
    }
}
