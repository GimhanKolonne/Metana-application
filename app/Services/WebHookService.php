<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebhookService
{
    protected $endpointUrl;

    public function __construct()
    {
        $this->endpointUrl = 'https://rnd-assignment.automations-3d6.workers.dev/';
    }

    /**
     * Send webhook notification
     */
    public function send(array $payload, string $candidateEmail)
    {
        return Http::withHeaders([
            'X-Candidate-Email' => $candidateEmail,
            'Content-Type' => 'application/json',
        ])->post($this->endpointUrl, $payload);
    }
}