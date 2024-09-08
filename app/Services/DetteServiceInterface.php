<?php

namespace App\Services;

interface DetteServiceInterface
{
    public function createDetteWithDetails(array $data);
    public function getDettesByStatus(string $statut);
    public function getAllDettes();
}
