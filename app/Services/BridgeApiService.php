<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
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
     * Prépare le client HTTP avec les headers obligatoires pour Bridge API v3.
     * @throws Exception
     */
    protected function getClient(): PendingRequest
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("Les identifiants Bridge (Client ID / Secret) ne sont pas configurés.");
        }

        return Http::withHeaders([
            'Client-Id' => $this->clientId,
            'Client-Secret' => $this->clientSecret,
            'Bridge-Version' => $this->bridgeVersion,
            'Accept' => 'application/json',
        ])->timeout(10); // Timeout de sécurité pour ne pas bloquer l'application
    }

    /**
     * Génère le lien de connexion sécurisé (Bridge Connect).
     * En v3, l'endpoint typique est POST /links ou /connect/items/add
     * * @return string L'URL de redirection Bridge
     * @throws Exception
     */
    public function createConnectLink(): string
    {
        $response = $this->getClient()->post("{$this->baseUrl}/connect/items/add");

        if ($response->failed()) {
            throw new Exception("Impossible de créer le lien Bridge Connect (v3) : " . $response->body());
        }

        return $response->json('redirect_url');
    }

    /**
     * Récupère les transactions d'un compte spécifique.
     * * @param string $bridgeAccountId L'ID du compte chez Bridge
     * @param string|null $since Date au format ISO 8601 (ex: 2023-10-01T00:00:00Z)
     * @return array La liste des transactions
     * @throws Exception
     */
    public function getTransactions(string $bridgeAccountId, ?string $since = null): array
    {
        $query = [];

        // La v3 utilise généralement "updated_since" ou "since" pour filtrer
        if ($since) {
            $query['updated_since'] = $since;
        }

        $response = $this->getClient()->get("{$this->baseUrl}/accounts/{$bridgeAccountId}/transactions", $query);

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des transactions Bridge : " . $response->body());
        }

        // Bridge renvoie généralement une structure paginée avec un tableau "resources" ou "data"
        return $response->json('resources') ?? $response->json('data') ?? [];
    }

    /**
     * (Optionnel) Récupère les détails du compte (IBAN, Nom) depuis son ID.
     * Très utile lors du callback pour enregistrer le bon nom de compte en base.
     * @throws Exception
     */
    public function getAccountDetails(string $bridgeAccountId): array
    {
        $response = $this->getClient()->get("{$this->baseUrl}/accounts/{$bridgeAccountId}");

        if ($response->failed()) {
            throw new Exception("Erreur lors de la récupération des infos du compte : " . $response->body());
        }

        return $response->json();
    }
}
