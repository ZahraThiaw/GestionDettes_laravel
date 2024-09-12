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

    public function getArchivedDebts()
    {
        // Récupérer toutes les collections de la base MongoDB
        $collections = $this->mongoDb->listCollections();

        if (empty($collections)) {
            return ['error' => 'Aucune collection d\'archives trouvée.'];
        }

        $allArchivedDebts = [];

        // Parcourir chaque collection et récupérer les dettes archivées
        foreach ($collections as $collection) {
            $debts = $this->mongoDb->{$collection->getName()}->find()->toArray();
            $allArchivedDebts = array_merge($allArchivedDebts, $debts);
        }

        if (empty($allArchivedDebts)) {
            return ['error' => 'Aucune dette archivées trouvées.'];
        }

        return $allArchivedDebts; // Retourner toutes les dettes archivées
    }

    public function getArchivedDebtsByClient($clientId)
    {
        // Parcourir toutes les collections d'archives
        $collections = $this->mongoDb->listCollections();
        $clientArchivedDebts = [];

        foreach ($collections as $collection) {
            $debts = $this->mongoDb->{$collection->getName()}->find(['client.id' => $clientId])->toArray();
            if (!empty($debts)) {
                $clientArchivedDebts = array_merge($clientArchivedDebts, $debts);
            }
        }

        if (empty($clientArchivedDebts)) {
            return ['error' => 'Aucune dette trouvée pour ce client.'];
        }

        return $clientArchivedDebts;
    }

    public function getArchivedDebtById($debtId)
    {
        // Parcourir les collections pour trouver la dette par ID
        $collections = $this->mongoDb->listCollections();

        foreach ($collections as $collection) {
            $debt = $this->mongoDb->{$collection->getName()}->findOne(['id' => $debtId]);
            if ($debt) {
                return $debt;
            }
        }

        return ['error' => 'Aucune dette trouvée avec cet ID.'];
    }

    //restaurer toutes les dettes archivées d'une date spécifique.
    public function restoreArchivedDebtsByDate($date)
    {
        $collectionName = $date;
        $debts = $this->mongoDb->$collectionName->find()->toArray();

        foreach ($debts as $debt) {
            $this->restoreLocalDebt($debt);
        }

        // Supprimer la collection après restauration
        $this->mongoDb->$collectionName->drop();
    }

    //restaurer une dette archivée ainsi que ses articles et paiements localement 
    protected function restoreLocalDebt($archivedDebt)
    {
        // Restaure la dette dans la base locale
        $dette = Dette::create([
            'id' => $archivedDebt['id'],
            'date' => $archivedDebt['date'],
            'montant' => $archivedDebt['montant'],
            'client_id' => $archivedDebt['client']['id'],
        ]);

        // Ajouter les articles à la dette
        foreach ($archivedDebt['articles'] as $article) {
            $dette->articles()->attach($article['articleId'], [
                'qteVente' => $article['qteVente'],
                'prixVente' => $article['prixVente'],
            ]);
        }

        // Ajouter les paiements
        foreach ($archivedDebt['paiements'] as $paiement) {
            $dette->paiements()->create([
                'montant' => $paiement['montant'],
                'date' => $paiement['date'],
            ]);
        }
    }

    //restaurer une seule dette depuis MongoDB vers la base locale
    public function restoreArchivedDebt($debtId)
    {
        $collections = $this->mongoDb->listCollections();

        foreach ($collections as $collection) {
            $debt = $this->mongoDb->{$collection->getName()}->findOne(['id' => $debtId]);

            if ($debt) {
                $this->restoreLocalDebt($debt);
                $this->mongoDb->{$collection->getName()}->deleteOne(['id' => $debtId]); // Supprimer la dette dans MongoDB
                return;
            }
        }
    }

    //restaurer toutes les dettes d'un client dans la base de données locale
    public function restoreArchivedDebtsByClient($clientId)
    {
        $collections = $this->mongoDb->listCollections();

        foreach ($collections as $collection) {
            $debts = $this->mongoDb->{$collection->getName()}->find(['client.id' => $clientId])->toArray();

            foreach ($debts as $debt) {
                $this->restoreLocalDebt($debt);
                $this->mongoDb->{$collection->getName()}->deleteOne(['id' => $debt['id']]);
            }
        }
    }
}
