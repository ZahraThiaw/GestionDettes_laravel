<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepositoryImpl implements ArticleRepository
{
    public function all()
    {
        return Article::whereNull('deleted_at')->get();
    }

    public function create(array $data)
    {
        return Article::create($data);
    }

    public function find($id)
    {
        return Article::where('id', $id)->whereNull('deleted_at')->first();
    }

    public function update($id, array $data)
    {
        $article = $this->find($id);
        if ($article) {
            $article->update($data);
        }
        return $article;
    }

    public function delete($id)
    {
        $article = $this->find($id);
        if ($article) {
            $article->delete();
        }
        else {
            return null;
        }
        return $article;

    }

    // public function findByLibelle($libelle)
    // {
    //     return Article::where('libelle', 'LIKE', "%{$libelle}%")->whereNull('deleted_at')->first();
    // }

    public function findByLibelle($libelle)
    {
        return Article::where('libelle', '=', $libelle)
                    ->whereNull('deleted_at')
                    ->first();
    }

    public function findByEtat($etat)
    {
        if ($etat === 'oui') {
            // Articles disponibles (qteStock > 0)
            return Article::where('qteStock', '>', 0)->whereNull('deleted_at')->get();
        } elseif ($etat === 'non') {
            // Articles non disponibles (qteStock = 0)
            return Article::where('qteStock', '=', 0)->whereNull('deleted_at')->get();
        }

        // Si la valeur n'est ni 'oui' ni 'non', retournez une collection vide
        return collect();
    }


    // public function updateStock(array $articlesData)
    // {
    //     $updatedArticles = [];
    //     foreach ($articlesData as $articleData) {
    //         $article = $this->find($articleData['id']);
    //         if ($article) {
    //             $article->qteStock += $articleData['qteStock'];
    //             $article->save();
    //             $updatedArticles[] = $article;
    //         }
    //     }
    //     return $updatedArticles;
    // }

    public function updateStock(array $articlesData)
    {
        $updatedArticles = [];
        $notFoundArticles = [];

        foreach ($articlesData as $articleData) {
            $article = $this->find($articleData['id']);  // Recherche de l'article par ID
            if ($article) {
                // Mise à jour de la quantité en stock
                $article->qteStock += $articleData['qteStock'];
                $article->save();
                $updatedArticles[] = $article;  // Ajout aux articles mis à jour
            } else {
                $notFoundArticles[] = $articleData;  // Ajout aux articles non trouvés
            }
        }

        return [
            'success' => $updatedArticles,     // Liste des articles mis à jour
            'error' => $notFoundArticles       // Liste des articles non trouvés
        ];
    }

    public function restore($id)
    {
        return Article::onlyTrashed()->findOrFail($id)->restore();
    }

    // public function getAvailableQuantity(Article $article, $quantiteDemande)
    // {
    //     $quantiteStock = $article->qteStock;
    //     $quantiteSeuil = $article->quantite_seuil;

    //     $quantitedisponible = $quantiteStock - $quantiteSeuil;

    //     if ($quantiteDemande >= $quantitedisponible) {

    //         return $quantitedisponible;

    //     }
    // }

    public function getAvailableQuantity(Article $article, $quantiteDemande)
    {
        $quantiteStock = $article->qteStock;
        $quantiteSeuil = $article->quantite_seuil;

        $quantiteDisponible = $quantiteStock - $quantiteSeuil;

        // Vérifier si la quantité demandée est inférieure ou égale à la quantité disponible
        if ($quantiteDemande <= $quantiteDisponible) {
            return $quantiteDisponible; // Renvoie la quantité disponible
        }

        return null; // Indique que l'article ne peut pas satisfaire la demande
    }

}
