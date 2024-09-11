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
}
