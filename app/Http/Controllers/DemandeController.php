<?php

namespace App\Http\Controllers;

use App\Enums\StatutDemande;
use App\Http\Requests\StoreDemandeRequest;
use App\Models\Demande;
use App\Models\User;
use App\Notifications\RelanceDemandeNotification;
use App\Repositories\DebtRepository;
use App\Services\Contracts\DebtServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DemandeController extends Controller
{
    protected $debtService;
    protected $debtRepository;

    public function __construct(DebtServiceInterface $debtService, DebtRepository $debtRepository)
    {
        $this->debtService = $debtService;
        $this->debtRepository = $debtRepository;
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

    public function relancerDemande($id)
    {
        $result = $this->debtService->relancerDemande($id);

        return [$result, $result['HttpStatus']];
    }

    public function getAllClientDemandes(Request $request)
    {
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
        $demandes = $this->debtService->getAllClientDemandes($etat);

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

    public function getNotifications()
    {
        $result = $this->debtService->getBoutiquierNotifications();
        return [$result, $result['HttpStatus']];
    }


    public function checkDisponibilite($demandeId)
    {
        $result = $this->debtService->checkDisponibilite($demandeId);

        return[
            'status' => 'Success',
            'articles_disponibles' => $result['articles_disponibles'],
            'articles_non_disponibles' => $result['articles_non_disponibles']
        ];
    }

//     public function update(Request $request, $demandeId)
// {
//     // Valider les entrées
//     $request->validate([
//         'status' => ['required', 'in:' . implode(',', StatutDemande::values())], // Utilise les valeurs des statuts
//         'motif' => 'required_if:status,Annulée|string|max:255',
//     ]);

//     // Récupérer le statut et le motif de la requête
//     $status = $request->input('status');
//     $motif = $request->input('motif') ?? null;

//     // Vérifier si le statut peut être mis à jour
//     $demande = $this->debtRepository->findById($demandeId);

//     if (!$demande) {
//         return ['error' => 'Demande non trouvée'];
//     }

//     // Vérifier si le statut actuel de la demande permet la modification (seulement si "En cours")
//     if ($demande->status !== StatutDemande::EN_COURS) {
//         return ['error' => 'Impossible de valider ou annuler une demande qui n\'est pas en cours'];
//     }

//     // Appeler le service pour mettre à jour le statut de la demande
//     $result = $this->debtService->updateDemandeStatus($demandeId, $status, $motif);

//     return response()->json($result);
// }


public function update(Request $request, $demandeId)
    {
        // Valider les entrées
        $request->validate([
            'status' => ['required', 'in:' . implode(',', StatutDemande::values())],
            'motif' => 'required_if:status,Annulée|string|max:255',
        ]);

        // Récupérer le statut et le motif de la requête
        $status = $request->input('status');
        $motif = $request->input('motif') ?? null;

        // Vérifier si la demande existe
        $demande = $this->debtRepository->findById($demandeId);

        if (!$demande) {
            return ['error' => 'Demande non trouvée'];
        }

        // Vérifier si le statut actuel de la demande permet la modification (seulement si "En cours")
        if ($demande->status !== StatutDemande::EN_COURS) {
            return ['error' => 'Impossible de valider ou annuler une demande qui n\'est pas en cours'];
        }

        // Mettre à jour le statut de la demande
        $result = $this->debtService->updateDemandeStatus($demandeId, $status, $motif);

        // Si la demande est validée, créer une dette correspondante et notifier le client
        if ($status === StatutDemande::VALIDEE) {
            // Créer une dette basée sur la demande
            $this->debtService->createDebtFromDemande($demande);

            // Envoyer une notification au client
            $this->debtService->notifyClientOfDebtReady($demande);
        }

        return response()->json($result);
    }


}

