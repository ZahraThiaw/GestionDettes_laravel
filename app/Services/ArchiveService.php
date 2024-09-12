<?php

namespace App\Services;

use App\Services\Contracts\IDebtArchivingService;

class ArchiveService
{
    protected $firebaseService;
    protected $mongoService;

    public function __construct(IDebtArchivingService $firebaseService, IDebtArchivingService $mongoService)
    {
        $this->firebaseService = $firebaseService;
        $this->mongoService = $mongoService;
    }

    public function archiveSettledDebts()
    {
        // Archiver les dettes réglées dans les deux bases de données
        $this->firebaseService->archiveSettledDebts();
        $this->mongoService->archiveSettledDebts();
    }

    public function getArchivedDebts()
    {
        $firebaseDebts = $this->firebaseService->getArchivedDebts();
        $mongoDebts = $this->mongoService->getArchivedDebts();

        // Fusionner ou gérer les résultats en fonction des besoins
        return [
            'firebase' => $firebaseDebts,
            'mongo' => $mongoDebts
        ];
    }

    public function getArchivedDebtsByClient($clientId)
    {
        $firebaseDebts = $this->firebaseService->getArchivedDebtsByClient($clientId);
        $mongoDebts = $this->mongoService->getArchivedDebtsByClient($clientId);

        // Fusionner ou gérer les résultats en fonction des besoins
        return [
            'firebase' => $firebaseDebts,
            'mongo' => $mongoDebts
        ];
    }

    public function getArchivedDebtById($debtId)
    {
        $firebaseDebt = $this->firebaseService->getArchivedDebtById($debtId);
        $mongoDebt = $this->mongoService->getArchivedDebtById($debtId);

        // Fusionner ou gérer les résultats en fonction des besoins
        return [
            'firebase' => $firebaseDebt,
            'mongo' => $mongoDebt
        ];
    }

    public function restoreArchivedDebtsByDate($date)
    {
        // Restaurer les dettes archivées à une date spécifique dans les deux bases de données
        $this->firebaseService->restoreArchivedDebtsByDate($date);
        $this->mongoService->restoreArchivedDebtsByDate($date);
    }

    public function restoreArchivedDebt($debtId)
    {
        // Restaurer une dette archivées par son ID dans les deux bases de données
        $this->firebaseService->restoreArchivedDebt($debtId);
        $this->mongoService->restoreArchivedDebt($debtId);
    }

    public function restoreArchivedDebtsByClient($clientId)
    {
        // Restaurer les dettes d'un client spécifique dans les deux bases de données
        $this->firebaseService->restoreArchivedDebtsByClient($clientId);
        $this->mongoService->restoreArchivedDebtsByClient($clientId);
    }
}
