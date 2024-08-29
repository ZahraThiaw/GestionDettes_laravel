<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\Client;
use App\Models\User;
use App\Traits\Response;
use App\Enums\StatutResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importer la façade DB pour les transactions

class ClientController extends Controller
{
    use Response;

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

        // Inclure les utilisateurs associés
        if ($request->has('include') && $request->query('include') === 'user') {
            $clients = $query->with('user')->get();
        } else {
            $clients = $query->get();
        }

        return $this->sendResponse($clients, StatutResponse::Success, 'Liste des clients chargée avec succès.');
    }

    public function show($id, Request $request)
    {
        if ($request->has('include') && $request->query('include') === 'user') {
            $client = Client::with('user')->findOrFail($id);
        } else {
            $client = Client::findOrFail($id);
        }

        return $this->sendResponse($client, StatutResponse::Success, 'Client récupéré avec succès.');
    }


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
            return $this->sendResponse($client, StatutResponse::Success, 'Client créé avec succès.', 201);

        } catch (\Exception $e) {
            // En cas d'erreur, rollback la transaction
            DB::rollBack();

            // Retourner la réponse d'erreur
            return $this->sendResponse(null, StatutResponse::Echec, 'Erreur lors de la création du client : ' . $e->getMessage(), 500);
        }
    }



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

        return $this->sendResponse($client, StatutResponse::Success, 'Client mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        if ($client->user_id) {
            User::find($client->user_id)->delete();
        }
        $client->delete();

        return $this->sendResponse(null, StatutResponse::Success, 'Client supprimé avec succès.');
    }
}


// public function store(StoreRequest $request)
//     {
//         DB::beginTransaction(); // Démarrer la transaction

//         try {
//             // Création du client
//             $clientData = $request->only(['surnom', 'telephone', 'adresse']);
//             $clientId = DB::table('clients')->insertGetId($clientData);

//             // Si les données de l'utilisateur sont présentes dans la requête
//             if ($request->has('user')) {
//                 $userData = $request->get('user');
//                 $userData['role'] = 'Client'; // Assurez-vous que le rôle est Client

//                 // Création de l'utilisateur associé
//                 $userId = DB::table('users')->insertGetId($userData);

//                 // Mise à jour du client avec l'ID utilisateur
//                 DB::table('clients')->where('id', $clientId)->update(['user_id' => $userId]);
//             }

//             // Si tout s'est bien passé, on confirme la transaction
//             DB::commit();

//             // Récupérer le client créé (avec ou sans utilisateur)
//             $client = DB::table('clients')->where('id', $clientId)->first();

//             return $this->sendResponse($client, StatutResponse::Success, 'Client créé avec succès.', 201);

//         } catch (\Exception $e) {
//             // En cas d'erreur, rollback la transaction
//             DB::rollBack();

//             return $this->sendResponse(null, StatutResponse::Error, 'Erreur lors de la création du client : ' . $e->getMessage(), 500);
//         }
//     }