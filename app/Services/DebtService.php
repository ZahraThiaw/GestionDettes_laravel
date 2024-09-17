<?php

namespace App\Services;

use App\Enums\StatutDemande;
use App\Exceptions\ServiceException;
use App\Models\Client;
use App\Models\Demande;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Services\Contracts\DebtServiceInterface;
use App\Jobs\NotifyBoutiquiersOfDebtRequest;
use App\Models\Dette;
use App\Models\User;
use App\Notifications\ArticlesDisponiblesNotification;
use App\Notifications\DebtRequestReadyNotification;
use App\Notifications\DemandeAnnulationNotification;
use App\Notifications\DemandeStatusUpdatedNotification;
use App\Notifications\DemandeValidationNotification;
use App\Notifications\RelanceDemandeNotification;
use App\Repositories\ArticleRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class DebtService implements DebtServiceInterface
{
    protected $debtRepository;
    protected $articleRepository;
    protected $detteService;

    public function __construct(DebtRepositoryInterface $debtRepository, ArticleRepository $articleRepository, DetteServiceInterface $detteService)
    {
        $this->debtRepository = $debtRepository;
        $this->articleRepository = $articleRepository;
        $this->detteService = $detteService;
    }

    public function handleDebtRequest(array $data, Client $client)
    {
        // Vérifier la catégorie du client
        $montantRestant = $this->debtRepository->getClientDebtsWithRemainingAmount($client);
        $categorie = $client->categorie; // Accès à la catégorie via la relation

        if (!$categorie) {
            throw ValidationException::withMessages([
                'error' => 'Catégorie de client non trouvée.',
            ]);
        }

        switch ($categorie->libelle) {
            case 'Gold':
                // Les clients Gold peuvent faire autant de demandes qu'ils veulent
                $demande = $this->processDebtRequest($data, $client);
                break;

            case 'Silver':
                // Les clients Silver peuvent faire une demande si leur montant restant n'atteint pas le montant_max de dette
                //$montantMaxDette = 5000;  // Exemple d'un montant max
                $montantMaxDette = $client->max_montant;
                if ($montantRestant >= $montantMaxDette) {
                    throw ValidationException::withMessages([
                        'error' => 'Vous avez atteint votre montant maximum de dette autorisé.',
                    ]);
                }
                $demande = $this->processDebtRequest($data, $client);
                break;

            case 'Bronze':
                // Les clients Bronze ne peuvent faire une demande que s'ils n'ont pas de dettes en cours
                if ($montantRestant > 0) {
                    throw ValidationException::withMessages([
                        'error' => 'Vous avez déjà une dette en cours. Veuillez la rembourser avant de soumettre une nouvelle demande.',
                    ]);
                }
                $demande = $this->processDebtRequest($data, $client);
                break;

            default:
                throw ValidationException::withMessages([
                    'error' => 'Catégorie de client non valide.',
                ]);
        }

        return $demande;
    }

    private function processDebtRequest(array $data, Client $client): Demande
    {
        // Créer la demande de dette
        $demande = $this->debtRepository->createDebtRequest([
            'date' => now(),
            'client_id' => $client->id,
            'articles' => $data['articles'],
        ]);

        // Dispatchez le job pour notifier les boutiquiers
        NotifyBoutiquiersOfDebtRequest::dispatch($demande);

        return $demande;
    }
    
    public function getClientDemandes(Client $client, ?string $etat = null)
    {
        return $this->debtRepository->getClientDemandes($client->id, $etat);
    }

    // public function relancerDemande(int $demandeId)
    // {
    //     // Récupérer la demande par son ID
    //     $demande = $this->debtRepository->findDemandeById($demandeId);

    //     // Vérifier si la demande existe
    //     if (!$demande) {
    //         return [
    //             'status' => 'Echec',
    //             'message' => 'Demande non trouvée.',
    //             'HttpStatus' => 404
    //         ];
    //     }

    //     // Débogage pour vérifier le statut
    //     // dd($demande->status, StatutDemande::ANNULEE->value);

    //     // Vérifier si le statut de la demande est annulé
    //     if ($demande->status !== StatutDemande::ANNULEE) {
    //         return [
    //             'status' => 'Echec',
    //             'message' => 'Seules les demandes annulées peuvent être relancées.',
    //             'HttpStatus' => 400
    //         ];
    //     }

    //     // Vérifier si la demande a été annulée il y a plus de 2 jours
    //     $annulationDate = $demande->updated_at;
    //     $deuxJoursApresAnnulation = $annulationDate->addDays(2);

    //     if (now()->lt($deuxJoursApresAnnulation)) {
    //         return [
    //             'status' => 'Echec',
    //             'message' => 'La relance ne peut être envoyée que 2 jours après l\'annulation.',
    //             'HttpStatus' => 400
    //         ];
    //     }

    //     // Créer une nouvelle demande avec les mêmes détails
    //     $nouvelleDemande = Demande::create([
    //         'client_id' => $demande->client_id,
    //         'date' => now(),
    //         'status' => 'En cours',
    //     ]);

    //     // Copier les articles associés de l'ancienne demande à la nouvelle
    //     foreach ($demande->articles as $article) {
    //         $nouvelleDemande->articles()->attach($article->id, ['qte' => $article->pivot->qte]);
    //     }

    //     // Récupérer tous les utilisateurs ayant le rôle Boutiquier
    //     $boutiquiers = User::whereHas('role', function($query) {
    //         $query->where('name', 'Boutiquier');
    //     })->get();

    //     // Envoi de la notification à tous les Boutiquiers
    //     Notification::send($boutiquiers, new RelanceDemandeNotification($nouvelleDemande));

    //     return [
    //         'status' => 'Success',
    //         'message' => 'Nouvelle demande créée et notification de relance envoyée à tous les Boutiquiers.',
    //         'nouvelle_demande' => $nouvelleDemande
    //     ];
    // }

    public function relancerDemande(int $demandeId)
{
    // Récupérer la demande par son ID
    $demande = $this->debtRepository->findDemandeById($demandeId);

    // Vérifier si la demande existe
    if (!$demande) {
        return [
            'status' => 'Echec',
            'message' => 'Demande non trouvée.',
            'HttpStatus' => 404
        ];
    }

    // Vérifier si le statut de la demande est annulé
    if ($demande->status !== StatutDemande::ANNULEE) {
        return [
            'status' => 'Echec',
            'message' => 'Seules les demandes annulées peuvent être relancées.',
            'HttpStatus' => 400
        ];
    }

    // Vérifier si la demande a été annulée dans les deux jours précédents
    $annulationDate = $demande->updated_at;
    $deuxJoursAvant = now()->subDays(2);

    if ($annulationDate->lt($deuxJoursAvant)) {
        return [
            'status' => 'Echec',
            'message' => 'La relance ne peut être envoyée que dans les deux jours suivant l\'annulation.',
            'HttpStatus' => 400
        ];
    }

    // Créer une nouvelle demande avec les mêmes détails
    $nouvelleDemande = Demande::create([
        'client_id' => $demande->client_id,
        'date' => now(),
        'status' => 'En cours'
    ]);

    // Copier les articles associés de l'ancienne demande à la nouvelle
    foreach ($demande->articles as $article) {
        $nouvelleDemande->articles()->attach($article->id, ['qte' => $article->pivot->qte]);
    }

    // Récupérer tous les utilisateurs ayant le rôle Boutiquier
    $boutiquiers = User::whereHas('role', function($query) {
        $query->where('name', 'Boutiquier');
    })->get();

    // Envoi de la notification à tous les Boutiquiers
    Notification::send($boutiquiers, new RelanceDemandeNotification($nouvelleDemande));

    return [
        'status' => 'Success',
        'message' => 'Nouvelle demande créée et notification de relance envoyée à tous les Boutiquiers.',
        'nouvelle_demande' => $nouvelleDemande,
        'HttpStatus' => 200
    ];
}


    public function getAllClientDemandes(?string $etat = null)
    {
        return $this->debtRepository->getAllClientDemandes( $etat);
    }

    public function getBoutiquierNotifications()
    {
        $user = auth()->user();

        return ['status' => 'Success', 'notifications' => $user->unreadNotifications, 'HttpStatus' => 200];
    }

    public function checkDisponibilite($demandeId)
    {
        $demande = $this->debtRepository->findById($demandeId);

        $articlesNonDisponibles = [];
        $articlesDisponibles = [];

        foreach ($demande->articles as $article) {
            $quantiteDemande = $article->pivot->qte; // Assurez-vous d'accéder à la quantité demandée via pivot si vous utilisez une relation many-to-many

            $quantiteDisponible = $this->articleRepository->getAvailableQuantity($article, $quantiteDemande);

            if ($quantiteDisponible !== null) {
                // Article disponible pour la demande
                $articlesDisponibles[] = [
                    'libelle' => $article->libelle,
                    'quantite_demande' => $quantiteDemande,
                ];
            } else {
                // Article non disponible ou partiellement disponible
                $articlesNonDisponibles[] = [
                    'libelle' => $article->libelle,
                    'quantite_demande' => $quantiteDemande,
                    // 'quantite_stock' => $article->qteStock,
                    // 'quantite_seuil' => $article->quantite_seuil,
                ];
            }
        }

        // Si certains articles ne sont pas disponibles, notifier le client
        if (!empty($articlesNonDisponibles)) {
            $client = $demande->client;
            Notification::send($client, new ArticlesDisponiblesNotification($articlesDisponibles));
        }

        return [
            'articles_disponibles' => $articlesDisponibles,
            'articles_non_disponibles' => $articlesNonDisponibles,
        ];
    }



    // public function updateDemandeStatus($demandeId, $status, $motif = null)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Récupérer la demande
    //         $demande = $this->debtRepository->findById($demandeId);

    //         if (!$demande) {
    //             throw new ServiceException("Demande non trouvée.");
    //         }

    //         // Mettre à jour le statut de la demande
    //         $demande->status = $status;
    //         $demande->save();

    //         // Notifier le client
    //         $client = $demande->client;

    //         if ($status === 'validée') {
    //             // Créer une dette correspondante à la demande validée
    //             $this->createDetteFromDemande($demande);

    //             // Envoyer une notification pour prendre les produits
    //             Notification::send($client, new DemandeValidationNotification($demande, $motif));
    //         } else {
    //             // Envoyer une notification pour l'annulation
    //             Notification::send($client, new DemandeAnnulationNotification($motif));
    //         }

    //         DB::commit();
    //         return [
    //             'status' => 'Success',
    //             'message' => 'Demande mise à jour avec succès.',
    //             'HttpStatus' => 200
    //         ];
    //     } catch (ServiceException $e) {
    //         DB::rollBack();
    //         throw new ServiceException("Erreur lors de l'enregistrement de la demande : " . $e->getMessage());
    //     }
    // }


//     public function updateDemandeStatus($demandeId, $status, $motif = null)
// {
//     DB::beginTransaction();
    
//     try {
//         // Récupérer la demande à partir du dépôt (repository)
//         $demande = $this->debtRepository->findById($demandeId);
        
//         if (!$demande) {
//             throw new ServiceException("Demande non trouvée.");
//         }

//         // Vérifier si le statut actuel est "En cours"
//         if ($demande->status !== StatutDemande::EN_COURS) {
//             throw new ServiceException("La demande ne peut être ni validée ni annulée car elle n'est pas en cours.");
//         }

//         // Vérifier si le nouveau statut est valide
//         if (!in_array($status, StatutDemande::values())) {
//             throw new ServiceException("Statut invalide.");
//         }

//         // Mettre à jour le statut de la demande (assurez-vous que $status est une chaîne de caractères)
//         $demande->status = (string) $status;

//         // Récupérer le client pour envoyer la notification
//         $client = $demande->client;

//         // Si le statut est annulé, envoyer la notification d'annulation
//         if ($status === StatutDemande::ANNULEE) {
//             Notification::send($client, new DemandeAnnulationNotification($motif));
//         }
//         // Si le statut est validé, créer la dette et envoyer la notification de validation
//         elseif ($status === StatutDemande::VALIDEE) {
//             $this->createDetteFromDemande($demande);
//             Notification::send($client, new DemandeValidationNotification($demande, $motif));
//         }

//         // Sauvegarder la mise à jour du statut
//         $demande->save();

//         DB::commit();
        
//         return ['status' => 'success', 'message' => 'Demande mise à jour avec succès.'];

//     } catch (ServiceException $e) {
//         DB::rollBack();
//         throw new ServiceException("Erreur lors de la mise à jour de la demande : " . $e->getMessage());
//     }
// }


public function updateDemandeStatus(int $demandeId, string $status, ?string $motif = null)
    {
        // Utiliser une transaction pour garantir l'intégrité des données
        DB::beginTransaction();

        try {
            // Trouver la demande
            $demande = Demande::find($demandeId);

            if (!$demande) {
                return [
                    'statut' => 'Echec',
                    'message' => 'Demande non trouvée.',
                    'HttpStatus' => 404
                ];
            }

            // Vérifier si le statut actuel de la demande permet la modification
            if ($demande->status !== StatutDemande::EN_COURS) {
                return [
                    'statut' => 'Echec',
                    'message' => 'Impossible de mettre à jour une demande qui n\'est pas en cours.',
                    'HttpStatus' => 400
                ];
            }

            // Mettre à jour le statut de la demande
            $demande->status = $status;

            // Ajouter un motif si la demande est annulée
            if ($status === StatutDemande::ANNULEE) {
                $demande->motif_annulation = $motif;
            }

            $demande->save();

            // Envoi de notification au client
            $client = $demande->client;
            Notification::send($client, new DemandeStatusUpdatedNotification($status, $motif));

            // Commit the transaction
            DB::commit();

            return [
                'statut' => 'Success',
                'message' => 'Statut de la demande mis à jour et notification envoyée avec succès.',
                'HttpStatus' => 200
            ];
        } catch (\Exception $e) {
            // Rollback en cas d'erreur
            DB::rollBack();

            return [
                'statut' => 'Echec',
                'message' => 'Erreur lors de la mise à jour du statut de la demande : ' . $e->getMessage(),
                'HttpStatus' => 500
            ];
        }
    }

    protected function createDetteFromDemande(Demande $demande)
{
    $data = [
        'clientId' => $demande->client_id,
        'date' => now(),
        'articles' => $demande->articles->map(function ($article) {
            return [
                'articleId' => $article->id,
                'qteVente' => $article->pivot->qte,
                'prixVente' => $article->prix,
            ];
        })->toArray(),
    ];

    // Créer la dette
    $this->detteService->createDetteWithDetails($data);
}

    public function createDebtFromDemande(Demande $demande)
    {
        // // Créer une nouvelle dette basée sur les informations de la demande
        // $debt = new Dette();
        // $debt->client_id = $demande->client_id;
        // $debt->montant_total = $demande->articles->sum(function($article) {
        //     return $article->pivot->qte * $article->prix;
        // });
        // $debt->save();

        // // Associer les articles à la dette
        // foreach ($demande->articles as $article) {
        //     $debt->articles()->attach($article->id, ['quantite' => $article->pivot->qte]);
        // }

        // return $debt;

        $data = [
            'clientId' => $demande->client_id,
            'date' => now(),
            'articles' => $demande->articles->map(function ($article) {
                return [
                    'articleId' => $article->id,
                    'qteVente' => $article->pivot->qte,
                    'prixVente' => $article->prix,
                ];
            })->toArray(),
        ];
    
        // Créer la dette
        $this->detteService->createDetteWithDetails($data);
    }

    public function notifyClientOfDebtReady(Demande $demande)
    {
        // Obtenir le client associé à la demande
        $client = $demande->client;

        // Envoyer la notification au client
        Notification::send($client, new DebtRequestReadyNotification($demande));
    }


}
