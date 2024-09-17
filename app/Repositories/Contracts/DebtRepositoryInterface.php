<?php

namespace App\Repositories\Contracts;

use App\Models\Client;

interface DebtRepositoryInterface
{
    public function createDebtRequest(array $data);

    public function getClientDebtsWithRemainingAmount(Client $client);
    public function getClientDemandes(int $clientId, ?string $etat = null);
    public function findDemandeById(int $demandeId);
}
