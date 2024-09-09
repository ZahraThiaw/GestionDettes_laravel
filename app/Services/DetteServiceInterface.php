<?php

namespace App\Services;

interface DetteServiceInterface
{
    public function createDetteWithDetails(array $data);
    public function getDettesByStatus(string $statut);
    public function getAllDettes();
    public function getDetteById(int $id);
    public function getArticlesByDetteId(int $detteId);
    public function getPaiementsByDetteId(int $detteId);
    public function addPaiementToDette(int $detteId, array $paiementData);
}
