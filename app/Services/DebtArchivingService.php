<?php

namespace App\Services;

use App\Models\Dette;
use MongoDB\Client as MongoClient;
use App\Http\Resources\DetteResource;
use App\Services\Contracts\IDebtArchivingService;
use Carbon\Carbon;

class DebtArchivingService implements IDebtArchivingService
{
    protected $mongoClient;
    protected $mongoDb;

    public function __construct()
    {
        $this->mongoClient = new MongoClient(env('MONGODB_DSN'));
        $this->mongoDb = $this->mongoClient->selectDatabase(env('MONGODB_DATABASE'));
    }

    /**
     * Check for settled debts and archive them in MongoDB.
     */
    public function archiveSettledDebts()
    {
        // Fetch debts where the remaining amount is zero
        $settledDebts = Dette::with(['paiements', 'articles', 'client']) // Load related data
            ->get()
            ->filter(function ($dette) {
                $totalPaiements = $dette->paiements->sum('montant');
                $montantRestant = $dette->montant - $totalPaiements;
                return $montantRestant == 0; // Keep only fully paid debts
            });

        // Archive each settled debt in MongoDB and then delete it from local DB
        foreach ($settledDebts as $dette) {
            $this->archiveDebtInMongo($dette);
            $this->deleteLocalDebt($dette);
        }
    }

    protected function archiveDebtInMongo($dette)
    {
        // Charger les relations nécessaires (paiements, articles, client) dans la dette
        $dette->load('paiements', 'articles', 'client');

        // Créer un tableau avec les données au format souhaité
        $archiveData = [
            'id' => $dette->id,
            'date' => $dette->date,
            'montant' => $dette->montant,
            'client' => [
                'id' => $dette->client->id,
                'surnom' => $dette->client->surnom,
                'telephone' => $dette->client->telephone,
                'adresse' => $dette->client->adresse,
                'user' => [
                    'nom' => $dette->client->user->nom,
                    'prenom' => $dette->client->user->prenom,
                    'login' => $dette->client->user->login,
                    'role_id' => $dette->client->user->role_id,
                    'photo' => $dette->client->user->photo,
                ],
            ],
            'articles' => $dette->articles->map(function ($article) {
                return [
                    'articleId' => $article->id,
                    'qteVente' => $article->pivot->qteVente,
                    'prixVente' => $article->pivot->prixVente,
                ];
            })->toArray(),
            'paiements' => $dette->paiements->map(function ($paiement) {
                return [
                    'id' => $paiement->id,
                    'montant' => $paiement->montant,
                    'date' => $paiement->date,
                ];
            })->toArray(),
        ];

        // Récupérer la date du jour pour nommer la collection
        $collectionName = Carbon::now()->format('Y-m-d');

        // Insérer les données formatées dans la collection MongoDB
        $this->mongoDb->$collectionName->insertOne($archiveData);
    }

    /**
     * Delete the debt and its related records (articles and payments) from the local database.
     */
    protected function deleteLocalDebt($dette)
    {
        // First, delete related articles and payments
        $dette->articles()->detach(); // Detach related articles from the pivot table
        $dette->paiements()->delete(); // Delete associated payments

        // Finally, delete the debt itself
        $dette->delete();
    }
}
