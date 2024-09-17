<?php

namespace App\Services;

use App\Enums\StatutDemande;
use App\Models\Client;
use App\Models\Demande;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Services\Contracts\DebtServiceInterface;
use App\Jobs\NotifyBoutiquiersOfDebtRequest;
use App\Models\User;
use App\Notifications\RelanceDemandeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class DebtService implements DebtServiceInterface
{
    protected $debtRepository;

    public function __construct(DebtRepositoryInterface $debtRepository)
    {
        $this->debtRepository = $debtRepository;
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
                $montantMaxDette = 5000;  // Exemple d'un montant max
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

        return $demande;
        // Dispatchez le job pour notifier les boutiquiers
        NotifyBoutiquiersOfDebtRequest::dispatch($demande);
    }
    
    public function getClientDemandes(Client $client, ?string $etat = null)
    {
        return $this->debtRepository->getClientDemandes($client->id, $etat);
    }

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

    // Débogage pour vérifier le statut
   // dd($demande->status, StatutDemande::ANNULEE->value);

    // Vérifier si le statut de la demande est annulé
    if ($demande->status !== StatutDemande::ANNULEE) {
        return [
            'status' => 'Echec',
            'message' => 'Seules les demandes annulées peuvent être relancées.',
            'HttpStatus' => 400
        ];
    }

    // Vérifier si la demande a été annulée il y a plus de 2 jours
    $annulationDate = $demande->updated_at;
    $deuxJoursApresAnnulation = $annulationDate->addDays(2);

    if (now()->lt($deuxJoursApresAnnulation)) {
        return [
            'status' => 'Echec',
            'message' => 'La relance ne peut être envoyée que 2 jours après l\'annulation.',
            'HttpStatus' => 400
        ];
    }

    // Créer une nouvelle demande avec les mêmes détails
    $nouvelleDemande = Demande::create([
        'client_id' => $demande->client_id,
        'date' => now(),
        'status' => 'En cours',
    ]);

    // Copier les articles associés de l'ancienne demande à la nouvelle
    foreach ($demande->articles as $article) {
        $nouvelleDemande->articles()->attach($article->id, ['qte' => $article->pivot->qte]);
    }

    // Récupérer tous les utilisateurs ayant le rôle Boutiquier
    $boutiquiers = User::whereHas('roles', function($query) {
        $query->where('name', 'Boutiquier');
    })->get();

    // Envoi de la notification à tous les Boutiquiers
    Notification::send($boutiquiers, new RelanceDemandeNotification($nouvelleDemande));

    return [
        'status' => 'Success',
        'message' => 'Nouvelle demande créée et notification de relance envoyée à tous les Boutiquiers.',
        'nouvelle_demande' => $nouvelleDemande
    ];
}
}
