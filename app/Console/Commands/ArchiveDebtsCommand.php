<?php

namespace App\Console\Commands;

use App\Services\ArchiveDette;
use Illuminate\Console\Command;

class ArchiveDebtsCommand extends Command
{
    protected $signature = 'debts:archive';
    protected $description = 'Archive settled debts';

    private $archiveDette;

    public function __construct(ArchiveDette $archiveDette)
    {
        parent::__construct();
        $this->archiveDette = $archiveDette;
    }

    public function handle()
    {
        $this->info('Archiving settled debts...');
        
        try {
            $this->archiveDette->archiveSettledDebts();
            $this->info('Debts archived successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
