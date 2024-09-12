<?php

namespace App\Services\Contracts;

interface IDebtArchivingService
{
    /**
     * Archive settled debts by moving them to MongoDB.
     *
     * @return void
     */
    public function archiveSettledDebts();
    public function getArchivedDebts();
    public function getArchivedDebtsByClient($clientId);
    public function getArchivedDebtById($debtId);
    public function restoreArchivedDebtsByDate($date);
    public function restoreArchivedDebt($debtId);
    public function restoreArchivedDebtsByClient($clientId);
}
