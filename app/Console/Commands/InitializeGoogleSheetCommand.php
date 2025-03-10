<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetService;
use Illuminate\Console\Command;

class InitializeGoogleSheetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheet:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the Google Sheet with headers';

    /**
     * Execute the console command.
     */
    public function handle(GoogleSheetService $sheetService): int
    {
        $this->info('Initializing Google Sheet...');
        
        try {
            $sheetService->initializeSheet();
            $this->info('Google Sheet initialized successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to initialize Google Sheet: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}