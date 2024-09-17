<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\SmsDette;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $smsDette;
    public function __construct(SmsDette $smsDette)
    {
        $this->smsDette = $smsDette;
    }
    
    public function getUnreadNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'statut' => 'Error',
                'message' => 'Client non authentifié',
                'httpStatus' => 401
            ];
        }

        // Obtenez le modèle Client associé à l'utilisateur
        $client = $user->client;
        
        //dd($client);
        if (!$client) {
            return [
                'statut' => 'Error',
                'data' => null,
                'message' => 'Client non trouvé pour l\'utilisateur.',
                'httpStatus' => 404
            ];
        }

        // Obtenez les notifications non lues du client
        $unreadNotifications = $client->unreadNotifications;

        if($unreadNotifications->isEmpty()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucune notification non lue pour le client.',
                'httpStatus' => 404
            ];
        }

        // Marquez les notifications comme lues
        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        // Retournez les notifications sous format JSON
        return [
            'statut' => 'Success',
            'data' => $unreadNotifications,
            'message' => 'Notifications non lues du client récupérées avec succès.',
            'httpStatus' => 200
        ];
    }

    public function getReadNotifications()
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'statut' => 'Error',
                'message' => 'Client non authentifié',
                'httpStatus' => 401
            ];
        }

        // Obtenez le modèle Client associé à l'utilisateur
        $client = $user->client;

        //dd($client);

        if (!$client) {
            return [
                'statut' => 'Error',
                'data' => null,
                'message' => 'Client non trouvé pour l\'utilisateur.',
                'httpStatus' => 404
            ];
        }

        // Obtenez les notifications lues du client
        $readNotifications = $client->readNotifications;

        if ($readNotifications->isEmpty()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucune notification lue pour le client.',
                'httpStatus' => 404
            ];
        }

        // Retournez les notifications sous format JSON
        return [
            'statut' => 'Success',
            'data' => $readNotifications,
            'message' => 'Notifications lues du client récupérées avec succès.',
            'httpStatus' => 200
        ];
    }

    public function sendToOneClient($id)
    {
        // Rechercher le client par son ID
        $client = Client::find($id);

        if (!$client) {
            return[
                'status' => 'error',
                'message' => 'Client non trouvé',
            ];
        }

        // Utiliser le service pour envoyer une notification de rappel
        $notification = $this->smsDette->sendDebtReminderToOneClient($client);

        if (!$notification) {
            return [
                'status' => 'info',
                'message' => 'Le client n\'a pas de dettes non soldées.',
            ];
        }

        // Retourner la notification envoyée si tout est valide
        return[
            'status' => 'success',
            'message' => 'Notification envoyée au client.',
            'notification' => [
                'client_surnom' => $client->surnom,
                'client_telephone' => $client->telephone,
            ],
        ];
    }

    public function sendToSpecificClients(Request $request)
    {
        // Récupérer les IDs de clients fournis dans la requête
        $clientIds = $request->input('client_ids');

        if (empty($clientIds) || !is_array($clientIds)) {
            return [
                'status' => 'error',
                'message' => 'Veuillez fournir une liste valide d\'ID de clients.'
            ];
        }

        // Récupérer les clients à partir des IDs
        $clients = Client::whereIn('id', $clientIds)->get();

        // Appeler le service pour envoyer les rappels et récupérer les résultats
        $result = $this->smsDette->sendDebtRemindersToClients($clients);

        // Retourner la réponse avec les 3 listes
        return [
            'status' => 'success',
            'message' => 'Notifications envoyées aux clients avec des dettes.',
            'clients_avec_dettes' => $result['clients_avec_dettes'],
            'clients_sans_dettes' => $result['clients_sans_dettes'],
            'clients_invalides' => $result['clients_invalides']
        ];
    }


    public function sendCustomMessageToClients(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'client_ids' => 'required|array',
            //'client_ids.*' => 'exists:clients,id', // Vérifier que les IDs existent dans la base
            'message' => 'required|string|max:255',
        ]);

        // Récupérer les IDs de clients et le message personnalisé
        $clientIds = $validatedData['client_ids'];
        $customMessage = $validatedData['message'];

        // Appeler le service pour envoyer les messages
        $result = $this->smsDette->sendCustomMessageToClients($clientIds, $customMessage);

        // Retourner la réponse au format JSON
        return [
            'status' => 'success',
            'message' => 'Notifications envoyées aux clients avec le message personnalisé.',
            'clients_avec_dettes' => $result['clients_avec_dettes'],
            'clients_sans_dettes' => $result['clients_sans_dettes'],
            'clients_invalides' => $result['clients_invalides']
        ];
        
    }

    // public function getBoutiquierNotifications()
    // {
    //     // Récupérer l'utilisateur authentifié
    //     $user = auth()->user();

    //     // // Vérifier si l'utilisateur a le rôle de Boutiquier
    //     // if (!$user->hasRole('Boutiquier')) {
    //     //     return [
    //     //         'status' => 'Echec',
    //     //         'message' => 'Vous n\'êtes pas autorisé à consulter ces notifications.',
    //     //         'HttpStatus' => 403
    //     //     ];
    //     // }

    //     // Récupérer les notifications non lues de l'utilisateur
    //     $notifications = $user->unreadNotifications;

    //     return [
    //         'status' => 'Success',
    //         'notifications' => $notifications,
    //         'HttpStatus' => 200
    //     ];
    // }

    public function getBoutiquierNotifications()
    {
        $user = Auth::user();

        // Obtenez les notifications non lues du client
        $notifications = $user->unreadNotifications;

        if($notifications->isEmpty()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucune notification de demandes soumis.',
                'httpStatus' => 404
            ];
        }

        // Marquez les notifications comme lues
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        // Retournez les notifications sous format JSON
        return [
            'statut' => 'Success',
            'data' => $notifications,
            'message' => 'Notifications de demandes soumis récupérées avec succès.',
            'httpStatus' => 200
        ];
    }

}
