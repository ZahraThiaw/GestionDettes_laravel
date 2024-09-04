<?php

namespace App\Services;

use App\Models\Article;

interface ArticleService
{
    public function all();
    public function find($id);
    public function create(array $data); 
    public function update($id, array $data);
    public function delete($id);
    public function findByLibelle($libelle);
    public function findByEtat($etat);
    public function updateStock(array $articlesData);
    public function restore($id);
}
