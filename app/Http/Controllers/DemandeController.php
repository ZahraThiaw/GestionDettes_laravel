<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Models\Demande;
use App\Models\User;
use App\Notifications\RelanceDemandeNotification;
use App\Services\Contracts\DebtServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DemandeController extends Controller
{
    protected $debtService;

    public function __construct(DebtServiceInterface $debtService)
    {
        $this->debtService = $debtService;
    }

    public function store(StoreDemandeRequest $request)
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();
        
        // Assurez-vous que l'utilisateur a un client associé
        if (!$user->client) {
            return [
                'status' => 'Echec',
                'message' => 'Client non trouvé pour l’utilisateur.',
                'HttpStatus' => 404
            ];
        }

        // Récupérer le client associé
        $client = $user->client;

        // Gérer la demande de dette via le service
        $demande = $this->debtService->handleDebtRequest($request->validated(), $client);

        return [
            'status' => 'Success',
            'demande' => $demande,
            'message' => 'Demande de dette soumise avec succès.',
            'HttpStatus' => 200
        ];
    }

    public function index(Request $request)
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();
        
        // Assurez-vous que l'utilisateur a un client associé
        if (!$user->client) {
            return [
                'status' => 'Echec',
                'message' => 'Client non trouvé pour l’utilisateur.',
                'HttpStatus' => 404
            ];
        }

        // Récupérer le client associé
        $client = $user->client;

        // Récupère l'état depuis les paramètres de la requête, s'il est fourni
        $etat = $request->query('etat');

        // Valider l'état s'il est fourni
        if ($etat && !in_array($etat, ['En cours', 'Annulée'])) {
            return [
                'statut' => 'Echec',
                'message' => 'État invalide.',
                'HttpStatus' => 400
            ];
        }

        // Utilise le service pour récupérer les demandes
        $demandes = $this->debtService->getClientDemandes($client, $etat);

        if ($demandes->isEmpty()) {
            return [
                'statut' => 'Echec',
                'message' => 'Aucune demande de dette n’a été trouvé pour le client.',
                'HttpStatus' => 404
            ];
        }

        return [
            'statut' => 'Success',
            'data' => $demandes,
            'message' => 'Demandes recupérées avec succès.',
            'HttpStatus' => 200
        ];
    }

    // public function relancerDemande(int $demandeId)
    // {
    //     // Récupérer la demande par son ID
    // $demande = Demande::find($demandeId);

    // // Vérifier si la demande existe
    // if (!$demande) {
    //     return response()->json([
    //         'status' => 'Echec',
    //         'message' => 'Demande non trouvée.',
    //         'HttpStatus' => 404
    //     ]);
    // }

    // // Vérifier si le statut de la demande est annulé
    // if ($demande->status !== 'Annulée') {
    //     return response()->json([
    //         'status' => 'Echec',
    //         'message' => 'Seules les demandes annulées peuvent être relancées.',
    //         'HttpStatus' => 400
    //     ]);
    // }

    //     // Vérifier si la demande a été annulée il y a plus de 2 jours
    //     $annulationDate = Carbon::parse($demande->updated_at);
    //     $deuxJoursApresAnnulation = $annulationDate->addDays(2);

    //     if (now()->lt($deuxJoursApresAnnulation)) {
    //         return [
    //             'status' => 'Echec',
    //             'message' => 'La relance ne peut être envoyée que 2 jours après l\'annulation.',
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
    //     $boutiquiers = User::whereHas('roles', function($query) {
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

    public function relancerDemande($id)
    {
        $result = $this->debtService->relancerDemande($id);

        return [$result, $result['HttpStatus']];
    }


}
