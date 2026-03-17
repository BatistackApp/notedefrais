<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OcrService
{
    public function analyzeReceipt(string $filePath): array
    {
        $apiKey = config('services.google.api_key');
        $model = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $imageData = base64_encode(file_get_contents($filePath));
            $mimeType = mime_content_type($filePath);

            $prompt = "Analyse ce ticket de caisse et extrais les informations suivantes au format JSON strict :
                - title (nom de l'enseigne/marchand)
                - amount_total (nombre décimal, montant TTC)
                - amount_taxe (nombre décimal, montant de la TVA)
                - tax_rate (nombre décimal, le taux de TVA en pourcentage, ex: 20)
                - expensed_at (date au format YYYY-MM-DD)
                - vehicle_id (Recherche si tu voie une plaque d'immatriculation)
                - odometer (Recherche une série de chiffre manuscrite avec 'km' à la fin et affiche la en nombre entier)
                - category_id (Catégorie de frais en français)

                Si une information est manquante, retourne null pour ce champ.";

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inlineData' => [
                                        'mimeType' => $mimeType,
                                        'data' => $imageData,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('Erreur API Gemini: '.$response->body());
            }

            $result = $response->json();
            $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            return json_decode($textResponse, true) ?? [];
        } catch (Throwable $e) {
            Log::error("Échec de l'analyse Gemini OCR : ".$e->getMessage());

            return [];
        }
    }
}
