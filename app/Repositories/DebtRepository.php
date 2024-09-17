<?php

namespace App\Repositories;

use App\Models\Demande;
use App\Models\Client;
use App\Enums\StatutDemande;
use App\Http\Resources\DemandeResource;
use App\Models\Article;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Exception;

class DebtRepository implements DebtRepositoryInterface
{
    public function createDebtRequest(array $data)
    {
        // Créer la demande de dette
        $demande = Demande::create([
            'date' => $data['date'],
            'client_id' => $data['client_id'],
            'status' => StatutDemande::EN_COURS->value, // Utilisation de l'énumération
        ]);

        // Traiter les articles
        foreach ($data['articles'] as $articleData) {
            // Rechercher l'article par libelle
            $article = Article::where('libelle', $articleData['libelle'])->first();
            
            if ($article) {
                $demande->articles()->attach($article->id, [
                    'qte' => $articleData['qte'],
                ]);
            } else {
                // Gérer le cas où l'article n'existe pas (log, exception, etc.)
                throw new Exception("L'article '{$articleData['libelle']}' n'existe pas.");
            }
        }

        return $demande;
    }


    public function getClientDebtsWithRemainingAmount(Client $client)
    {
        $dettes = $client->dettes()->with('paiements')->get();
        $montantRestant = 0;

        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant += $dette->montant - $totalPaiements;
        }

        return $montantRestant;
    }

    public function getClientDemandes(int $clientId, ?string $etat = null)
    {
        // Si aucun état n'est fourni, on utilise "En cours" par défaut
        $etat = $etat ?? 'En cours';

        // Créer la requête de base avec les articles associés
        $query = Demande::where('client_id', $clientId)
                        ->with('articles') // Inclure les articles associés
                        ->where('status', $etat); // Appliquer l'état, par défaut "En cours"

        // Retourner les demandes avec la ressource personnalisée
        return DemandeResource::collection($query->get());
    }

    public function findDemandeById(int $demandeId)
    {
        return Demande::find($demandeId);
    }

}
