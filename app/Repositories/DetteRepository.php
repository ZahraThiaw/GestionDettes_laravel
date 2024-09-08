<?php

namespace App\Repositories;

use App\Models\Dette;
use App\Models\Article;
use App\Models\ArticleDette;
use App\Models\Paiement;

class DetteRepository implements DetteRepositoryInterface
{
    public function createDette(array $data)
    {
        return Dette::create($data);
    }

    public function addArticleToDette(int $detteId, array $articleData)
    {
        // Trouver la dette par son ID
        $dette = Dette::find($detteId);

        // Utiliser la relation many-to-many pour attacher les articles à la dette avec les détails dans la table pivot
        $dette->articles()->attach($articleData['articleId'], [
            'qteVente' => $articleData['qteVente'],
            'prixVente' => $articleData['prixVente'],
        ]);
    }


    public function updateArticleStock(int $articleId, int $quantity)
    {
        $article = Article::find($articleId);
        if ($article) {
            $article->qteStock -= $quantity;
            $article->save();
        }
    }

    public function createPaiement(array $data)
    {
        Paiement::create($data);
    }

    
    public function getAllDettes()
    {
        return Dette::with(['articles', 'paiements'])->get();
    }

    public function getDettesByStatus(string $statut)
    {
        // Récupérer toutes les dettes avec les articles et paiements
        $dettes = $this->getAllDettes();

        // Filtrer les dettes selon le statut fourni
        return $dettes->filter(function ($dette) use ($statut) {
            // Calculer le montant total, montant des paiements et montant restant
            $montantTotal = $dette->montant;
            $montantPaiement = $dette->paiements->sum('montant');
            $montantRestant = $montantTotal - $montantPaiement;

            // Appliquer le filtre basé sur le statut
            if ($statut === 'Solde') {
                // Retourner les dettes dont le montant restant est égal à 0
                return $montantRestant == 0;
            } elseif ($statut === 'NonSolde') {
                // Retourner les dettes dont le montant restant est différent de 0
                return $montantRestant != 0;
            }

            // Si le statut n'est pas reconnu, ne retourner aucune dette
            return false;
        });
    }


}
