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
            $this->deleteLocalDebt($dette);
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

    public function getArchivedDebts()
    {
        // Récupérer toutes les archives depuis la racine 'archives'
        $archives = $this->database->getReference('archives')->getValue();

        if (empty($archives)) {
            return ['error' => 'Aucune dette archivées trouvées.'];
        }

        $allDebts = [];

        // Parcourir chaque collection de dettes archivées par date
        foreach ($archives as $date => $debts) {
            foreach ($debts as $debt) {
                $allDebts[] = $debt; // Ajouter chaque dette à la liste
            }
        }

        return $allDebts; // Retourner toutes les dettes archivées
    }

    public function getArchivedDebtsByClient($clientId)
    {
        // Récupérer toutes les archives
        $archives = $this->database->getReference('archives')->getValue();

        // Filtrer pour ne garder que les dettes du client spécifié
        $clientDebts = [];
        if ($archives) {
            foreach ($archives as $date => $debts) {
                foreach ($debts as $debt) {
                    if (isset($debt['client']['id']) && $debt['client']['id'] == $clientId) {
                        $clientDebts[] = $debt;
                    }
                }
            }
        }

        if (empty($clientDebts)) {
            return ['error' => 'Aucune dette trouvée pour ce client.'];
        }

        return $clientDebts;
    }

    public function getArchivedDebtById($debtId)
    {
        // Récupérer toutes les archives
        $archives = $this->database->getReference('archives')->getValue();

        // Chercher la dette par son ID
        if ($archives) {
            foreach ($archives as $date => $debts) {
                foreach ($debts as $debt) {
                    if (isset($debt['id']) && $debt['id'] == $debtId) {
                        return $debt; // Retourner la dette trouvée
                    }
                }
            }
        }

        return ['error' => 'Aucune dette trouvée avec cet ID.'];
    }

    //restaurer toutes les dettes archivées d'une date spécifique.
    public function restoreArchivedDebtsByDate($date)
    {
        // Récupérer les dettes archivées à cette date
        $debts = $this->database->getReference('archives/' . $date)->getValue();

        if ($debts) {
            foreach ($debts as $debt) {
                $this->restoreDebt($debt); // Restaurer chaque dette dans la base de données locale
            }
            
            // Supprimer les dettes archivées dans Firebase après restauration
            $this->database->getReference('archives/' . $date)->remove();
        }
        
        return ['error' => 'Aucune dette archivée trouvée à cette date.'];
    }

    // Restaurer une dette spécifique dans la base de données locale
    public function restoreArchivedDebt($debtId)
    {
        // Récupérer toutes les archives
        $archives = $this->database->getReference('archives')->getValue();

        if ($archives) {
            foreach ($archives as $date => $debts) {
                foreach ($debts as $key => $debt) {
                    if (isset($debt['id']) && $debt['id'] == $debtId) {
                        $this->restoreDebt($debt); // Restaurer la dette
                        // Supprimer la dette de Firebase après restauration
                        $this->database->getReference('archives/' . $date . '/' . $key)->remove();
                        return true;
                    }
                }
            }
        }

        return false;
    }

    //Restaurer les dettes d'un client dans la base de données locale
    public function restoreArchivedDebtsByClient($clientId)
    {
        // Récupérer toutes les archives
        $archives = $this->database->getReference('archives')->getValue();

        if ($archives) {
            foreach ($archives as $date => $debts) {
                foreach ($debts as $key => $debt) {
                    if (isset($debt['client']['id']) && $debt['client']['id'] == $clientId) {
                        $this->restoreDebt($debt); // Restaurer la dette
                        // Supprimer la dette de Firebase après restauration
                        $this->database->getReference('archives/' . $date . '/' . $key)->remove();
                    }
                }
            }
        }
    }

    // Restaurer une dette dans la base de données locale
    protected function restoreDebt($debt)
    {
        // Création de la dette dans la base locale
        $newDebt = new Dette();
        $newDebt->id = $debt['id'];
        $newDebt->date = $debt['date'];
        $newDebt->montant = $debt['montant'];
        $newDebt->client_id = $debt['client']['id'];
        $newDebt->save();

        // Restauration des articles associés
        foreach ($debt['articles'] as $article) {
            $newDebt->articles()->attach($article['articleId'], [
                'qteVente' => $article['qteVente'],
                'prixVente' => $article['prixVente'],
            ]);
        }

        // Restauration des paiements associés
        foreach ($debt['paiements'] as $paiement) {
            $newDebt->paiements()->create([
                'montant' => $paiement['montant'],
                'date' => $paiement['date'],
            ]);
        }
    }

}
