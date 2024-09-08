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
   
        /**
     * @OA\Get(
     *     path="/clients",
     *     summary="Liste des clients",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="Filtrer par numéro de téléphone (séparé par des virgules)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sortsurnom",
     *         in="query",
     *         description="Trier par surnom en ordre croissant",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort-surnom",
     *         in="query",
     *         description="Trier par surnom en ordre décroissant",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="comptes",
     *         in="query",
     *         description="Filtrer par présence d'un compte utilisateur (oui|non)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrer par état actif du compte utilisateur (oui|non)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Inclure les informations de l'utilisateur associé (user)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="surnom", type="string", example="Dupont"),
     *                     @OA\Property(property="telephone", type="string", example="0123456789"),
     *                     @OA\Property(property="adresse", type="string", example="123 Rue de la République"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Jean"),
     *                         @OA\Property(property="prenom", type="string", example="Claude"),
     *                         @OA\Property(property="login", type="string", example="jeanclaude"),
     *                         @OA\Property(property="password", type="string", example="password123"),
     *                         @OA\Property(property="role_id", type="integer", example=1),
     *                         @OA\Property(property="photo", type="string", example="profile.jpg")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liste des clients chargée avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun client trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Pas de clients.")
     *         )
     *     )
     * )
     */

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



     /**
     * @OA\Post(
     *     path="/clients/telephone",
     *     summary="Filtrer les clients par téléphone",
     *     tags={"Client"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="telephone", type="string", example="0123456789,0987654321"),
     *                 required={"telephone"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clients trouvés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="surnom", type="string", example="Dupont"),
     *                     @OA\Property(property="telephone", type="string", example="0123456789"),
     *                     @OA\Property(property="adresse", type="string", example="123 Rue de la République"),
     *                     @OA\Property(property="user", type="object", 
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Jean"),
     *                         @OA\Property(property="prenom", type="string", example="Claude"),
     *                         @OA\Property(property="login", type="string", example="jeanclaude"),
     *                         @OA\Property(property="password", type="string", example="password123"),
     *                         @OA\Property(property="role_id", type="integer", example=1),
     *                         @OA\Property(property="photo", type="string", example="profile.jpg")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Clients trouvés avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun client trouvé avec ces numéros de téléphone",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Aucun client trouvé avec ces numéros de téléphone.")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Get(
     *     path="/clients/{id}/user",
     *     summary="Afficher un client avec ses informations utilisateur",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client et utilisateur récupérés avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="client", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="surnom", type="string", example="Dupont"),
     *                 @OA\Property(property="telephone", type="string", example="0123456789"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue de la République"),
     *                 @OA\Property(property="user", type="object", 
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Jean"),
     *                     @OA\Property(property="prenom", type="string", example="Claude"),
     *                     @OA\Property(property="login", type="string", example="jeanclaude"),
     *                     @OA\Property(property="password", type="string", example="password123"),
     *                     @OA\Property(property="role_id", type="integer", example=1),
     *                     @OA\Property(property="photo", type="string", example="profile.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client et utilisateur récupérés avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client non trouvé.")
     *         )
     *     )
     * )
     */

    public function showClientWithUser($id)
    {
        try {
            $client = $this->clientService->getClientById($id, true);
            $clientData = [
                'client' => new ClientResource($client),
                'user' => $client->user ? new UserResource($client->user) : null,
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


/**
     * @OA\Get(
     *     path="/clients/{id}",
     *     summary="Afficher un client",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Inclure les informations utilisateur associées (user)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client récupéré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="client", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="surnom", type="string", example="Dupont"),
     *                 @OA\Property(property="telephone", type="string", example="0123456789"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue de la République"),
     *                 @OA\Property(property="user", type="object", 
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Jean"),
     *                     @OA\Property(property="prenom", type="string", example="Claude"),
     *                     @OA\Property(property="login", type="string", example="jeanclaude"),
     *                     @OA\Property(property="password", type="string", example="password123"),
     *                     @OA\Property(property="role_id", type="integer", example=1),
     *                     @OA\Property(property="photo", type="string", example="profile.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client récupéré avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client non trouvé.")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Post(
     *     path="/clients",
     *     summary="Créer un nouveau client",
     *     tags={"Client"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="telephone", type="string", example="0123456789"),
     *                 @OA\Property(property="user", type="object", @OA\Property(property="login", type="string", example="johndoe"), @OA\Property(property="password", type="string", example="password123")),
     *                 required={"name", "telephone"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client créé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur lors de la création du client",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la création du client.")
     *         )
     *     )
     * )
     */

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



     /**
     * @OA\Post(
     *     path="/clients/{clientId}/register",
     *     summary="Créer un compte utilisateur pour un client existant",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         description="ID du client pour lequel créer un compte utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="login", type="string", example="user123"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 required={"login", "password"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte utilisateur créé avec succès pour le client",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Compte utilisateur créé avec succès pour le client."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="surnom", type="string", example="Dupont"),
     *                 @OA\Property(property="telephone", type="string", example="0123456789"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue de la République"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="nom", type="string", example="Jean"),
        *                   @OA\Property(property="prenom", type="string", example="Claude"),
        *                   @OA\Property(property="login", type="string", example="jeanclaude"),
        *                   @OA\Property(property="password", type="string", example="password123"),
        *                   @OA\Property(property="role_id", type="integer", example=1),
        *                   @OA\Property(property="photo", type="string", example="profile.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide, données incorrectes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Requête invalide, données incorrectes.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé ou rôle 'Client' non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Client non trouvé ou rôle 'Client' non trouvé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflit, le client a déjà un compte utilisateur ou le login existe déjà",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ce client a déjà un compte utilisateur ou le login existe déjà.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur lors de la création du compte utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la création du compte utilisateur.")
     *         )
     *     )
     * )
     */

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
     


    /**
     * @OA\Put(
     *     path="/clients/{id}",
     *     summary="Mettre à jour un client existant",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="telephone", type="string", example="0123456789"),
     *                 @OA\Property(property="user", type="object", 
     *                 @OA\Property(property="login", type="string", example="johndoeupdated"),
     *                 @OA\Property(property="password", type="string", example="newpassword123")),
     *                 required={"name", "telephone"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client mis à jour avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur lors de la mise à jour du client",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la mise à jour du client.")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Delete(
     *     path="/clients/{id}",
     *     summary="Supprimer un client",
     *     tags={"Client"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client supprimé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Le client avec l'ID spécifié n'existe pas.")
     *         )
     *     )
     * )
     */

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

