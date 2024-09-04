<?php

namespace App\Services;

use App\Repositories\ArticleRepository;

class ArticleServiceImpl implements ArticleService
{
    protected $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function all()
    {
        return $this->articleRepository->all();
    }

    public function find($id)
    {
        return $this->articleRepository->find($id);
    }

    // Implémentation de la méthode create
    public function create(array $data)
    {
        return $this->articleRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->articleRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->articleRepository->delete($id);
    }

    public function findByLibelle($libelle)
    {
        return $this->articleRepository->findByLibelle($libelle);
    }

    public function findByEtat($etat)
    {
        return $this->articleRepository->findByEtat($etat);
    }

    public function updateStock(array $articlesData)
    {
        return $this->articleRepository->updateStock($articlesData);
    }

    public function restore($id)
    {
        return $this->articleRepository->restore($id);
    }
}
