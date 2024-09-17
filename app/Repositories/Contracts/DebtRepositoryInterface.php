<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use App\Models\Demande;

interface DebtRepositoryInterface
{
    public function createDebtRequest(array $data);

    public function getClientDebtsWithRemainingAmount(Client $client);
    public function getClientDemandes(int $clientId, ?string $etat = null);
    public function findDemandeById(int $demandeId);
    public function getAllClientDemandes(?string $etat = null);
    public function findById($id);
    public function createDetteFromDemande(Demande $demande);
}
