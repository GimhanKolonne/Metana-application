<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;
    protected $range;

    public function __construct()
    {
        $this->spreadsheetId = config('services.google.sheet_id');
        $this->range = 'A:J'; // Columns A through J
        
        $this->initializeClient();
    }

    /**
     * Initialize Google API client
     */
    private function initializeClient()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Metana Job Application Pipeline');
        $this->client->setScopes([Sheets::SPREADSHEETS]);
        $this->client->setAuthConfig(storage_path('app/google-credentials.json'));
        $this->client->setAccessType('offline');
        
        $this->service = new Sheets($this->client);

    }
    
    /**
     * Add a row to the Google Sheet
     */
    public function addRow(array $values)
    {
        $body = new ValueRange([
            'values' => [$values]
        ]);
        
        $params = [
            'valueInputOption' => 'RAW'
        ];
        
        return $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $this->range,
            $body,
            $params
        );
    }
    
    /**
     * Initialize the Google Sheet with headers if it doesn't exist
     */
    public function initializeSheet()
    {
        // Check if the sheet already has content
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'A1:J1'
        );
        
        if (empty($response->getValues())) {
            // Add headers if the sheet is empty
            $headers = [
                'ID',
                'Name',
                'Email',
                'Phone',
                'CV URL',
                'Education',
                'Qualifications',
                'Projects',
                'Personal Info',
                'Timestamp'
            ];
            
            $this->addRow($headers);
        }
    }
}