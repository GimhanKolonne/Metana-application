<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebhookService;

class WebhookTestController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function testWebhook()
    {
        $payload = [
            'message' => 'This is a test webhook payload',
            'status' => 'success',
        ];

        $response = $this->webhookService->send($payload, 'test@domain.com');

// Log the response status and body
\Log::info('Webhook response:', [
    'status' => $response->status(),
    'body' => $response->body(),
]);

// Check if the response was successful
if ($response->successful()) {
    return 'Webhook sent successfully!';
} else {
    return 'Failed to send webhook. Status: ' . $response->status() . ' Body: ' . $response->body();
}

    }
}
