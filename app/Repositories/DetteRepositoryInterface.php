<?php

namespace App\Repositories;

use App\Models\Dette;

interface DetteRepositoryInterface
{
    public function createDette(array $data);
    public function addArticleToDette(int $detteId, array $articleData);
    public function updateArticleStock(int $articleId, int $quantity);
    public function createPaiement(array $data);
    public function getAllDettes();
    public function getDettesByStatus(string $statut);
}
