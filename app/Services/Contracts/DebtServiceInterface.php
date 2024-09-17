<?php

namespace App\Services\Contracts;

use App\Models\Demande;
use App\Models\Client;

interface DebtServiceInterface
{
    public function handleDebtRequest(array $data, Client $client);
    public function getClientDemandes(Client $client, ?string $etat = null);
    public function relancerDemande(int $demandeId);
}
