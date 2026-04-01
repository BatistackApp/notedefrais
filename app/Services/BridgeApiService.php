<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BridgeApiService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $bridgeVersion;

    public function __construct()
    {
        // On s'assure d'utiliser la v3 par défaut
        $this->baseUrl = config('services.bridge.api_url', 'https://api.bridgeapi.io/v3');
        $this->clientId = config('services.bridge.client_id');
        $this->clientSecret = config('services.bridge.client_secret');

        // En v3, Bridge recommande fortement de spécifier la version de l'API dans les headers
        $this->bridgeVersion = '2025-01-15';
    }

    /**
     * 1. Client HTTP de base (SANS jeton Bearer)
     * Requis pour s'authentifier et créer des utilisateurs
     * @throws Exception
     */
    protected function getBaseClient(): PendingRequest
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("Les identifiants Bridge (Client ID / Secret) ne sont pas configurés.");
        }

        return Http::withHeaders([
            'Client-Id' => $this->clientId,
            'Client-Secret' => $this->clientSecret,
            'Bridge-Version' => $this->bridgeVersion,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(10);
    }

    /**
     * 2. Créer ou récupérer l'Utilisateur Système Bridge
     * BatiStack étant une application d'entreprise, on lie toutes les banques à un profil unique côté Bridge.
     */
    public function getSystemBridgeUserUuid(): string
    {
        $externalId = 'batistack_system_user';

        return Cache::rememberForever('bridge_system_user_uuid', function () use ($externalId) {
            $client = $this->getBaseClient();

            // Tentative de création
            $response = $client->post("{$this->baseUrl}/aggregation/users", [
                'external_user_id' => $externalId,
            ]);

            if ($response->successful()) {
                return $response->json('uuid');
            }

            // S'il existe déjà (Erreur 409), on récupère la liste pour trouver son UUID
            $usersResponse = $client->get("{$this->baseUrl}/aggregation/users");
            if ($usersResponse->successful()) {
                $users = $usersResponse->json('resources') ?? [];
                foreach ($users as $u) {
                    if (($u['external_user_id'] ?? '') === $externalId) {
                        return $u['uuid'];
                    }
                }
            }

            throw new Exception("Impossible de créer/retrouver l'utilisateur système Bridge : " . $response->body());
        });
    }

    /**
     * 3. Obtenir un jeton d'accès (Bearer Token) pour cet Utilisateur Spécifique
     */
    public function getUserAccessToken(): string
    {
        $userUuid = $this->getSystemBridgeUserUuid();

        // Le token Bridge dure 2h (7200s), on le met en cache pendant 1h50 (6600s) pour assurer le relai
        return Cache::remember('bridge_user_access_token', 6600, function () use ($userUuid) {
            $response = $this->getBaseClient()->post("{$this->baseUrl}/aggregation/authorization/token", [
                'user_uuid' => $userUuid,
            ]);

            if ($response->failed()) {
                throw new Exception("Erreur d'authentification Utilisateur Bridge : " . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * 4. Client HTTP final (AVEC jeton Bearer de l'utilisateur)
     * @throws Exception
     */
    protected function getAuthenticatedClient(): PendingRequest
    {
        return $this->getBaseClient()->withToken($this->getUserAccessToken());
    }

    /**
     * Génère le lien de connexion sécurisé (Bridge Connect Session)
     * @throws Exception
     */
    public function createConnectLink($localUser = null): string
    {
        $callbackUrl = route('bridge.callback');
        // En V3, l'authentification se fait via le Bearer Token et l'endpoint est /connect-sessions
        $response = $this->getAuthenticatedClient()->post("{$this->baseUrl}/aggregation/connect-sessions", [
            // Bridge demande un email obligatoire (DSP2) pour prévenir en cas d'expiration de connexion
            'user_email' => $localUser->email ?? 'admin@batistack.com',
            // --- AJOUT CRUCIAL ICI ---
            // On indique explicitement à Bridge où rediriger l'utilisateur après la sélection de la banque.
            'callback_url' => $callbackUrl,
        ]);

        if ($response->failed()) {
            throw new Exception("Erreur de création de la session Bridge Connect : " . $response->body());
        }

        // Renvoie l'URL vers laquelle rediriger l'administrateur
        return $response->json('url');
    }

    /**
     * Récupère les transactions du compte bancaire (Appelé par les Jobs nocturnes)
     * @throws Exception
     */
    public function getTransactions(string $bridgeAccountId, ?string $since = null): array
    {
        $query = [];
        if ($since) {
            $query['updated_since'] = $since;
        }
        $query['account_id'] = $bridgeAccountId;

        $response = $this->getAuthenticatedClient()->get("{$this->baseUrl}/aggregation/transactions", $query);

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des transactions : " . $response->body());
        }

        return $response->json('resources') ?? $response->json('data') ?? [];
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function getAccountsLists(): array
    {
        $response = $this->getAuthenticatedClient()->get("{$this->baseUrl}/aggregation/accounts");

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des infos des comptes : " . $response->body());
        }

        return $response->json();
    }

    /**
     * Récupère les détails d'un compte spécifique (Appelé lors du Callback)
     * @throws Exception
     */
    public function getAccountDetails(string $bridgeAccountId): array
    {
        $response = $this->getAuthenticatedClient()->get("{$this->baseUrl}/aggregation/accounts/{$bridgeAccountId}");

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des infos du compte : " . $response->body());
        }

        return $response->json();
    }

    public function getAccountDetailsFromItemId(string $itemId): array
    {
        $response = $this->getAuthenticatedClient()->get("{$this->baseUrl}/aggregation/accounts?item_id={$itemId}");

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des infos du compte : " . $response->body());
        }

        return $response->json();
    }
}
