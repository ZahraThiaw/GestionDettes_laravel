<?php

namespace App\Services;

use App\Services\Contracts\IDebtArchivingService;

class ArchiveDette
{
    protected $archivingService;

    public function __construct(IDebtArchivingService $archivingService)
    {
        $this->archivingService = $archivingService;
    }

    /**
     * Archive settled debts using the specified archiving service.
     */
    public function archiveSettledDebts()
    {
        $this->archivingService->archiveSettledDebts();
    }
}
