<?php

namespace App\Repositories;

use App\Models\Article;

interface ArticleRepository
{
    public function all();
    public function create(array $data);
    public function find($id);
    public function update($id, array $data);
    public function delete($id);
    public function findByLibelle($libelle);
    public function findByEtat($etat);
    public function updateStock(array $articlesData);
    public function restore($id);
}