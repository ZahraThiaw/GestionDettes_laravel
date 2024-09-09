<?php

namespace App\Services;

use App\Exceptions\ServiceException;
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
                throw new ServiceException("Aucun article associé à la dette.");
            }

            $montantTotal = 0;
            $validArticles = [];

            foreach ($data['articles'] as $article) {
                // Vérifier si l'article existe via le repository
                $articleModel = $this->articleRepository->find($article['articleId']);
                if (!$articleModel) {
                    throw new ServiceException("L'article avec ID {$article['articleId']} n'existe pas.");
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
                    throw new ServiceException("Le montant du paiement dépasse le montant de la dette.");
                }

                // Enregistrer le paiement
                $this->detteRepository->createPaiement([
                    'dette_id' => $dette->id,
                    'montant' => $montantPaiement,
                    'date' => now(),
                ]);
            }

            DB::commit();
        } catch (ServiceException $e) {
            DB::rollBack();
            throw new ServiceException("Erreur lors de l'enregistrement de la dette : " . $e->getMessage());
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

    public function getDetteById(int $id)
    {
        return $this->detteRepository->getDetteById($id);
    }

    public function getArticlesByDetteId(int $detteId)
    {
        return $this->detteRepository->getArticlesByDetteId($detteId);
    }

    public function getPaiementsByDetteId(int $detteId)
    {
        return $this->detteRepository->getPaiementsByDetteId($detteId);
    }

    public function addPaiementToDette(int $detteId, array $paiementData)
    {
        DB::beginTransaction();

        try {
            // Récupérer la dette
            $dette = $this->detteRepository->getDetteById($detteId);

            if (!$dette) {
                throw new ServiceException("Dette not found.");
            }

            // Calculer le montant total des paiements effectués
            $montantRestant = $dette->montant - $dette->paiements->sum('montant');

            // Vérifier que le paiement ne dépasse pas le montant restant
            if ($paiementData['montant'] > $montantRestant) {
                throw new ServiceException("Le montant du paiement dépasse le montant restant.");
            }

            // Ajouter la date actuelle aux données du paiement
            $paiementData['date'] = now(); // Utilise la fonction now() pour obtenir la date actuelle

            // Ajouter le paiement à la dette
            $this->detteRepository->createPaiementForDette($detteId, $paiementData);

            DB::commit();

            // Récupérer les paiements mis à jour
            $dette = $this->detteRepository->getDetteById($detteId);

            return [
                'id' => $dette->id,
                'date' => $dette->date,
                'montant' => $dette->montant,
                'paiements' => $dette->paiements
            ];
        } catch (ServiceException $e) {
            DB::rollBack();
            throw new ServiceException("Erreur lors de l'ajout du paiement : " . $e->getMessage());
        }
    }


}
