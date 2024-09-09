<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\Client;
use App\Models\User;
//use App\Traits\Response;
use App\Enums\StatutResponse;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Jobs\StoreImageInCloud;
use App\Models\Role;
use Illuminate\Http\Request;
//use App\Facades\ClientServiceFacade as ClientService;
use App\Mail\ClientLoyaltyCardMail;
use App\Rules\Telephone;
use App\Services\ClientServiceInterface;
use Illuminate\Support\Facades\DB; // Importer la façade DB pour les transactions
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientServiceInterface $clientService)
    {
        $this->clientService = $clientService;
    }
   // use Response;

    public function index(Request $request)
    {
        $filters = $request->only(['telephone', 'sortsurnom', 'sort-surnom', 'comptes', 'active', 'include']);
        $clients = $this->clientService->getAllClients($filters);

        if ($clients->isEmpty()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucun client trouvé.',
                'httpStatus' => 404
            ];
        }

        return [
            'statut' => 'Success',
            'data' => ClientResource::collection($clients),
            'message' => 'Liste des clients récupérée avec succès.',
            'httpStatus' => 200
        ];
    }

    public function filterByTelephone(Request $request)
    {
        // Validation des données de la requête
        $validatedData = $request->validate([
            'telephone' => ['required', new Telephone],
        ]);

        // Récupération du téléphone validé
        $telephone = $validatedData['telephone'];

        $client = $this->clientService->getClientByTelephone($telephone);

        if (!$client) {
            return [
                'statut' => StatutResponse::Echec,
                'data' => null,
                'message' => 'Client non trouvé',
                'httpStatus' => 404
            ];
        }

        return [
            'statut' => StatutResponse::Success,
            'data' => new ClientResource($client),
            'message' => 'Client trouvé',
            'httpStatus' => 200
        ];
    }

    public function showClientWithUser($id)
    {
        try {
            $client = $this->clientService->getClientById($id, true);
            $clientData = [
                'client' => new ClientResource($client)
            ];

            $status = $client->user ? 'Success' : 'Echec';
            $message = $client->user ? 'Client et utilisateur trouvés avec succès.' : 'Client trouvé avec succès, mais aucun utilisateur associé.';
            $httpStatus = $client->user ? 200 : 404;

            return [
                'statut' => $status,
                'data' => $clientData,
                'message' => $message,
                'httpStatus' => $httpStatus
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Le client avec l\'ID spécifié n\'existe pas.',
                'httpStatus' => 404
            ];
        }
    }

    public function show($id, Request $request)
    {
        try {
            $withUser = $request->has('include') && $request->query('include') === 'user';
            $client = $this->clientService->getClientById($id, $withUser);
            
            return [
                'statut' => 'Success',
                'data' => new ClientResource($client),
                'message' => 'Client trouvé avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Client non trouvé.',
                'httpStatus' => 404
            ];
        }
    }

public function store(StoreRequest $request)
{
    try {
        // Valider les données client et utilisateur grâce à StoreRequest
        $validatedData = $request->validated();

        // Extraire les données du client
        $clientData = [
            'surnom' => $validatedData['surnom'],
            'telephone' => $validatedData['telephone'],
            'adresse' => $validatedData['adresse'] ?? null,
        ];

        // Extraire les données utilisateur si elles existent
        $userData = $validatedData['user'] ?? null;

        // Vérifier si le login utilisateur existe déjà si des données utilisateur sont fournies
        if ($userData && User::where('login', $userData['login'])->exists()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Le nom d\'utilisateur est déjà pris.',
                'httpStatus' => 409
            ];
        }

        $client = $this->clientService->createClient([
            'client' => $clientData,
            'user' => $userData
        ]);

        // if (request()->hasFile('photo')) {
        //     $file = request()->file('photo');
        //     // Sauvegarder le fichier temporairement
        //     $tempPath = $file->store('temp');
        //     StoreImageInCloud::dispatch($userData, $tempPath);
        // }

        // Préparer les données à retourner
        $responseData = [
            'client' => $client,
        ];

        // Retourner la réponse avec succès
        return [
            'statut' => 'Success',
            'data' => $responseData,
            'message' => 'Client enregistré avec succès.',
            'httpStatus' => 200
        ];

    } catch (\Exception $e) {
        // Ajouter les données pour le débogage
        return [
            'statut' => 'Echec',
            'data' => [],
            'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage(),
            'httpStatus' => 500
        ];
    }
}

     public function registerClientForExistingClient(RegisterRequest $request, $clientId)
     {
         try {
             $userData = $request->validated();
             $this->clientService->registerClientForExistingClient($userData, $clientId);
             $client = Client::latest()->first();
             return [
                 'statut' => StatutResponse::Success,
                 'data' => new ClientResource($client),
                 'message' => 'Compte utilisateur créé avec succès pour le client.',
                 'httpStatus' => 201
             ];
         } catch (\Exception $e) {
             return [
                 'statut' => StatutResponse::Echec,
                 'data' => null,
                 'message' => 'Erreur lors de la création du compte : ' . $e->getMessage(),
                 'httpStatus' => 500
             ];
         }
     }

    public function update($id, UpdateRequest $request)
    {
        try {
            $client = $this->clientService->updateClient($id, $request->validated());

            return [
                'statut' => 'Success',
                'data' => new ClientResource($client->user),
                'message' => 'Client mis à jour avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Erreur lors de la mise à jour du client : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $this->clientService->deleteClient($id);

            return [
                'statut' => 'Success',
                'data' => [],
                'message' => 'Client supprimé avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Erreur lors de la suppression du client : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

}

