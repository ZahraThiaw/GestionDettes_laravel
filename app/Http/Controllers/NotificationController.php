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
            return response()->json([
                'status' => 'error',
                'message' => 'Client non trouvé',
            ], 404);
        }

        // Utiliser le service pour envoyer une notification de rappel
        $notification = $this->smsDette->sendDebtReminderToOneClient($client);

        if (!$notification) {
            return response()->json([
                'status' => 'info',
                'message' => 'Le client n\'a pas de dettes non soldées.',
            ], 200);
        }

        // Retourner la notification envoyée si tout est valide
        return response()->json([
            'status' => 'success',
            'message' => 'Notification envoyée au client.',
            'notification' => [
                'client_surnom' => $client->surnom,
                'client_telephone' => $client->telephone,
            ],
        ], 200);
    }

    public function sendToSpecificClients(Request $request)
    {
        // Récupérer les IDs de clients fournis dans la requête
        $clientIds = $request->input('client_ids');

        if (empty($clientIds) || !is_array($clientIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Veuillez fournir une liste valide d\'ID de clients.'
            ], 400);
        }

        // Récupérer les clients à partir des IDs
        $clients = Client::whereIn('id', $clientIds)->get();

        // Appeler le service pour envoyer les rappels et récupérer les résultats
        $result = $this->smsDette->sendDebtRemindersToClients($clients);

        // Retourner la réponse avec les 3 listes
        return response()->json([
            'status' => 'success',
            'message' => 'Notifications envoyées aux clients avec des dettes.',
            'clients_avec_dettes' => $result['clients_avec_dettes'],
            'clients_sans_dettes' => $result['clients_sans_dettes'],
            'clients_invalides' => $result['clients_invalides']
        ], 200);
    }
}
