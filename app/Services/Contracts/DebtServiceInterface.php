<?php

namespace App\Services\Contracts;

use App\Models\Demande;
use App\Models\Client;

interface DebtServiceInterface
{
    public function handleDebtRequest(array $data, Client $client);
    public function getClientDemandes(Client $client, ?string $etat = null);
    public function relancerDemande(int $demandeId);
    public function getAllClientDemandes(?string $etat = null);
    public function getBoutiquierNotifications();
    public function checkDisponibilite($demandeId);
    //public function updateDemandeStatus($demandeId, $status, $motif = null);

public function updateDemandeStatus(int $demandeId, string $status, ?string $motif = null);
    public function notifyClientOfDebtReady(Demande $demande);
    public function createDebtFromDemande(Demande $demande);

}
