<?php

namespace App\Services;

use App\Repositories\DetteRepositoryInterface;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class DetteService implements DetteServiceInterface
{
    protected $detteRepository;
    protected $articleRepository;

    public function __construct(DetteRepositoryInterface $detteRepository, ArticleRepository $articleRepository)
    {
        $this->detteRepository = $detteRepository;
        $this->articleRepository = $articleRepository;
    }

    public function createDetteWithDetails(array $data)
    {
        DB::beginTransaction();

        try {
            // Validation des articles
            if (empty($data['articles']) || count($data['articles']) === 0) {
                throw new Exception("Aucun article associé à la dette.");
            }

            $montantTotal = 0;
            $validArticles = [];

            foreach ($data['articles'] as $article) {
                // Vérifier si l'article existe via le repository
                $articleModel = $this->articleRepository->find($article['articleId']);
                if (!$articleModel) {
                    throw new Exception("L'article avec ID {$article['articleId']} n'existe pas.");
                }

                // Calculer le montant total de la dette
                $montantTotal += $article['qteVente'] * $article['prixVente'];

                // Ajouter l'article valide à la liste des articles
                $validArticles[] = $article;
            }

            // Créer la dette avec le montant calculé
            $detteData = [
                'client_id' => $data['clientId'],
                'montant' => $montantTotal,
                'date' => now(),
            ];
            $dette = $this->detteRepository->createDette($detteData);

            // Ajouter les articles à la dette et mettre à jour les stocks
            foreach ($validArticles as $article) {
                $this->detteRepository->addArticleToDette($dette->id, $article);

                // Mettre à jour la quantité en stock
                $this->detteRepository->updateArticleStock($article['articleId'], $article['qteVente']);
            }

            // Si un paiement est fourni
            if (!empty($data['paiement'])) {
                $montantPaiement = $data['paiement']['montant'];

                // Validation du montant du paiement
                if ($montantPaiement > $montantTotal) {
                    throw new Exception("Le montant du paiement dépasse le montant de la dette.");
                }

                // Enregistrer le paiement
                $this->detteRepository->createPaiement([
                    'dette_id' => $dette->id,
                    'montant' => $montantPaiement,
                    'date' => now(),
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Erreur lors de l'enregistrement de la dette : " . $e->getMessage());
        }
    }

    public function getDettesByStatus(string $statut)
    {
        return $this->detteRepository->getDettesByStatus($statut);
    }

    public function getAllDettes()
    {
        return $this->detteRepository->getAllDettes();
    }
}
