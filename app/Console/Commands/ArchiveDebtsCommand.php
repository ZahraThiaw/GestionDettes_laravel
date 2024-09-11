<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DebtArchivingService;

class ArchiveDebtsCommand extends Command
{
    protected $signature = 'debts:archive';
    protected $description = 'Archive settled debts in MongoDB';

    private $archivingService;

    public function __construct(DebtArchivingService $archivingService)
    {
        parent::__construct();
        $this->archivingService = $archivingService;
    }

    public function handle()
    {
        $this->info('Archiving settled debts...');
        
        try {
            $this->archivingService->archiveSettledDebts();
            $this->info('Debts archived successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
