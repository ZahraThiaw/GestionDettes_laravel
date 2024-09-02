<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\Client;
use App\Models\User;
use App\Traits\Response;
use App\Enums\StatutResponse;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importer la façade DB pour les transactions

class ClientController extends Controller
{
    use Response;


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
        $query = Client::query();

        // Filtrer par téléphone
        if ($request->has('telephone')) {
            $telephones = explode(',', $request->query('telephone'));
            $query->whereIn('telephone', $telephones);
        }

        // Tri par surnom ascendant (sortsurnom) ou descendant (sort-surnom)
        if ($request->has('sortsurnom')) {
            $query->orderBy('surnom', 'asc');
        }

        if ($request->has('sort-surnom')) {
            $query->orderBy('surnom', 'desc');
        }

        // Filtrer par la présence ou non d'un compte utilisateur (compte = oui|non)
        if ($request->has('comptes')) {
            $compte = $request->query('comptes');
            if ($compte === 'oui') {
                $query->whereNotNull('user_id'); // Clients avec compte utilisateur
            } elseif ($compte === 'non') {
                $query->whereNull('user_id'); // Clients sans compte utilisateur
            }
        }

        // Filtrer par compte actif ou non uniquement pour les clients ayant un compte utilisateur
        if ($request->has('active')) {
            $active = $request->query('active');
    
            // Ajouter une sous-requête pour filtrer sur les comptes actifs ou non
            $query->whereHas('user', function ($q) use ($active) {
                if ($active === 'oui') {
                    $q->where('active', true); // Compte actif
                } elseif ($active === 'non') {
                    $q->where('active', false); // Compte inactif
                }
            });
        }

        // Inclure les utilisateurs associés
        if ($request->has('include') && $request->query('include') === 'user') {
            $clients = $query->with('user')->get();
        } else {
            $clients = $query->get();
        }

        // Vérifier si la liste est vide
        if ($clients->isEmpty()) {
            return $this->sendResponse([], StatutResponse::Echec, 'Pas de clients.', 404);
        }

        return $this->sendResponse($clients, StatutResponse::Success, 'Liste des clients chargée avec succès.', 200);
    }

     /**
     * @OA\Post(
     *     path="/clients/filter-by-telephone",
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
        // Valider que le champ 'telephone' est présent et qu'il contient une liste de téléphones
        $this->validate($request, [
            'telephone' => 'required|string'
        ]);

        // Récupérer la liste des téléphones fournie dans la requête (séparée par des virgules)
        $telephones = explode(',', $request->input('telephone'));

        // Rechercher les clients qui correspondent aux numéros de téléphone
        $clients = Client::whereIn('telephone', $telephones)->get();

        // Vérifier si des clients ont été trouvés
        if ($clients->isEmpty()) {
            return $this->sendResponse([], StatutResponse::Echec, 'Aucun client trouvé avec ces numéros de téléphone.', 404);
        }

        // Retourner les clients trouvés
        return $this->sendResponse($clients, StatutResponse::Success, 'Clients trouvés avec succès.', 200);
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
            // Récupérer le client avec son compte utilisateur s'il existe
            $client = Client::with('user')->findOrFail($id);

            // Vérifier si le client a un utilisateur associé
            if ($client->user) {
                $clientData = [
                    'client' => new ClientResource($client),
                    'user' => new UserResource($client->user),
                ];

                return $this->sendResponse($clientData, StatutResponse::Success, 'Client et utilisateur trouvés avec succès.', 200);
            } else {
                $clientData = [
                    'client' => new ClientResource($client),
                    'user' => null,
                ];
                return $this->sendResponse($clientData, StatutResponse::Echec, 'Client trouvé avec succès, mais aucun utilisateur associé.', 411);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Retourner une erreur si l'ID du client n'existe pas
            return $this->sendResponse([], StatutResponse::Echec, 'Le client avec l\'ID spécifié n\'existe pas.', 404);
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
            // Vérifier si la requête contient le paramètre 'include' avec la valeur 'user'
            if ($request->has('include') && $request->query('include') === 'user') {
                // Récupérer le client avec les informations de l'utilisateur associé
                $client = Client::with('user')->findOrFail($id);
            } else {
                // Récupérer uniquement le client
                $client = Client::findOrFail($id);
            }

            // Retourner le client récupéré
            return $this->sendResponse($client, StatutResponse::Success, 'Client trouvé avec succès.', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Retourner un message d'erreur si le client n'existe pas
            return $this->sendResponse([], StatutResponse::Echec, 'Le client avec l\'ID spécifié n\'existe pas.', 404);
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
        // Démarrer une transaction
        DB::beginTransaction();

        try {
            // Obtenir les données validées
            $clientData = $request->validated();

            // Créer le client
            $client = Client::create($clientData);

            // Vérifier si des données utilisateur sont présentes
            if ($request->has('user')) {
                $userData = $clientData['user'];
                $userData['role'] = 'Client'; // Assurez-vous que le rôle est Client

                // Créer l'utilisateur
                $user = User::create($userData);

                // Associer l'utilisateur au client
                $client->user()->associate($user); // Associer l'utilisateur au client
                $client->save(); // Sauvegarder le client avec l'utilisateur associé
            }

            // Confirmer la transaction
            DB::commit();

            // Retourner la réponse
            return $this->sendResponse($client, StatutResponse::Success, 'Client créé avec succès.', 200);

        } catch (\Exception $e) {
            // En cas d'erreur, rollback la transaction
            DB::rollBack();

            // Retourner la réponse d'erreur
            return $this->sendResponse(null, StatutResponse::Echec, 'Erreur lors de la création du client : ' . $e->getMessage(), 500);
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
        // Démarrer une transaction pour assurer la cohérence des données
        DB::beginTransaction();

        try {
            // Récupérer le client existant à partir de son ID
            $client = Client::findOrFail($clientId);

            // Vérifier si le client a déjà un compte utilisateur
            if ($client->user) {
                return $this->sendResponse(null, StatutResponse::Echec, 'Ce client a déjà un compte utilisateur.', 409);
            }

            // Vérifier si le login existe déjà dans la base de données
            $userData = $request->validated();
            if (User::where('login', $userData['login'])->exists()) {
                return $this->sendResponse(null, StatutResponse::Echec, 'Le login existe déjà.', 409);
            }

            // Récupérer le rôle "Client" à partir de la table des rôles
            $roleClient = Role::where('name', 'Client')->first();
            if (!$roleClient) {
                return $this->sendResponse(null, StatutResponse::Echec, 'Le rôle "Client" est introuvable.', 500);
            }

            // Créer le compte utilisateur avec le rôle "Client"
            $userData['role_id'] = $roleClient->id; // Assigner l'ID du rôle client
            $user = User::create($userData);

            // Associer le compte utilisateur au client
            $client->user()->associate($user);
            $client->save();

            // Confirmer la transaction pour enregistrer les modifications
            DB::commit();

            // Retourner une réponse avec succès et les détails du client
            return $this->sendResponse(new ClientResource($client), StatutResponse::Success, 'Compte utilisateur créé avec succès pour le client.', 201);

        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            DB::rollBack();
            
            // Retourner une réponse d'échec avec l'erreur
            return $this->sendResponse(null, StatutResponse::Echec, 'Erreur lors de la création du compte : ' . $e->getMessage(), 500);
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
    public function update(UpdateRequest $request, $id)
    {
        $client = Client::findOrFail($id);

        $validatedData = $request->validated(); // Obtenez les données validées

        $clientData = $validatedData;
        $userData = $clientData['user'] ?? null;

        unset($clientData['user']); // Supprimer les données utilisateur des données client

        $client->update($clientData);

        if ($userData) {
            if ($client->user_id) {
                $user = User::find($client->user_id);
                $user->update($userData);
            } else {
                $userData['role'] = 'Client';
                $user = User::create($userData);
                $client->user_id = $user->id;
                $client->save();
            }
        }

        return $this->sendResponse($client, StatutResponse::Success, 'Client mis à jour avec succès.', 200);
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
        $client = Client::findOrFail($id);
        if ($client->user_id) {
            User::find($client->user_id)->delete();
        }
        $client->delete();

        return $this->sendResponse(null, StatutResponse::Success, 'Client supprimé avec succès.', 200);
    }
}

