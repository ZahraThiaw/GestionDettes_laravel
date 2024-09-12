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

    public function getArchivedDebts()
    {
        return$this->archivingService->getArchivedDebts();
    }

    public function getArchivedDebtsByClient($clientId)
    {
        return $this->archivingService->getArchivedDebtsByClient($clientId);
    }

    public function getArchivedDebtById($debtId)
    {
        return $this->archivingService->getArchivedDebtById($debtId);
    }

    public function restoreArchivedDebtsByDate($date)
    {
        // Restaurer les dettes archivées à une date spécifique dans les deux bases de données
        return $this->archivingService->restoreArchivedDebtsByDate($date);
    }

    public function restoreArchivedDebt($debtId)
    {
        // Restaurer une dette archivées par son ID dans les deux bases de données
        return $this->archivingService->restoreArchivedDebt($debtId);
    }

    public function restoreArchivedDebtsByClient($clientId)
    {
        // Restaurer les dettes d'un client spécifique dans les deux bases de données
        return $this->archivingService->restoreArchivedDebtsByClient($clientId);
    }

}
