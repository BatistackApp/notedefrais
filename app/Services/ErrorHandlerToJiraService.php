<?php

namespace App\Services;

use Http;
use Illuminate\Support\Facades\Log;

class ErrorHandlerToJiraService
{
    public function handle(\Throwable $exception, string $summary, string $description): void
    {
        try {
            $n8nWebhookUrl = config('services.n8n.webhook_jira_url');

            Http::timeout(5)->post($n8nWebhookUrl, [
                'project' => 'Note de Frais',
                'issue_type' => 'Bug',
                'summary' => $summary,
                'description' => $description."\n\n".
                    "Erreur : {$exception->getMessage()}\n".
                    "Fichier : {$exception->getFile()}:{$exception->getLine()}",
                'environment' => config('app.env'),
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (\Throwable $e) {
            Log::critical('Impossible de contacter n8n pour la création de ticket Jira.', ['error' => $e->getMessage()]);
        }
    }
}
