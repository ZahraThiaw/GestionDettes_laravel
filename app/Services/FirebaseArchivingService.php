<?php

namespace App\Services;

use App\Models\Dette;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use App\Services\Contracts\IDebtArchivingService;

class FirebaseArchivingService implements IDebtArchivingService
{
    protected $firebase;
    protected $database;

    public function __construct()
    {
        // Initialiser Firebase avec le fichier credentials.json à partir de la constante FIREBASE_CREDENTIALS_PATH
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS_PATH'))) // Utilisation de la constante FIREBASE_CREDENTIALS_PATH
            ->withDatabaseUri('https://laravel-dette-archive-default-rtdb.firebaseio.com'); // Remplacez par l'URL de votre Realtime Database

        // Accéder à Realtime Database
        $this->database = $firebase->createDatabase();
    }

    /**
     * Archive settled debts in Firebase Realtime Database.
     */
    public function archiveSettledDebts()
    {
        // Fetch debts where the remaining amount is zero
        $settledDebts = Dette::with(['paiements', 'articles', 'client'])
            ->get()
            ->filter(function ($dette) {
                $totalPaiements = $dette->paiements->sum('montant');
                $montantRestant = $dette->montant - $totalPaiements;
                return $montantRestant == 0; // Only fully paid debts
            });

        // Archive each settled debt in Firebase Realtime Database
        foreach ($settledDebts as $dette) {
            $this->archiveDebtInFirebase($dette);
        }
    }

    /**
     * Archive a single debt in Firebase Realtime Database.
     */
    protected function archiveDebtInFirebase($dette)
    {
        // Charger les relations nécessaires
        $dette->load('paiements', 'articles', 'client');

        // Créer les données formatées
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

        // Ajouter les données dans Realtime Database sous une collection avec la date du jour
        $collectionPath = 'archives/' . Carbon::now()->format('Y-m-d');
        $this->database->getReference($collectionPath)->push($archiveData);
    }

    protected function deleteLocalDebt($dette)
    {
        // First, delete related articles and payments
        $dette->articles()->detach(); // Detach related articles from the pivot table
        $dette->paiements()->delete(); // Delete associated payments

        // Finally, delete the debt itself
        $dette->delete();
    }
}
