<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Jobs\StoreImageInCloud;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        // Démarrer une transaction pour garantir l'intégrité des données
        DB::beginTransaction();

        try {
            // Obtenir les données validées de la requête
            $data = $request->validated();

            // Créer l'utilisateur avec les données validées, y compris le role_id
            $user = User::create($data);

            // Confirmer la transaction
            DB::commit();

            return [
                'statut' => 'success',
                'data' => $user,
                'message' => 'Utilisateur créé avec succès.',
                'httpStatus' => 201
            ];

        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();

            return [
                'statut' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function index(Request $request)
    {
        // Récupérer les paramètres de la requête
        $roleName = $request->query('role');
        $active = $request->query('active');

        // Construire la requête
        $query = User::query();

        // Filtrer par rôle si spécifié
        if ($roleName) {
            // Récupérer l'ID du rôle à partir du nom
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $query->where('role_id', $role->id);
            } else {
                // Retourner une erreur si le rôle n'existe pas
                return [
                    'statut' => 'error',
                    'data' => null,
                    'message' => 'Le rôle spécifié est invalide.',
                    'httpStatus' => 400
                ];
            }
        }

        // Filtrer par statut actif si spécifié
        if ($active !== null) {
            // Convertir 'oui' en true et 'non' en false
            $isActive = ($active === 'oui');
            $query->where('active', $isActive);
        }

        // Exécuter la requête et récupérer les résultats
        $users = $query->get();

        // Vérifier si des utilisateurs ont été trouvés
        if ($users->isEmpty()) {
            return [
                'statut' => 'error',
                'data' => null,
                'message' => 'Aucun utilisateur trouvé',
                'httpStatus' => 400
            ];
        }

        // Retourner la réponse JSON avec les utilisateurs trouvés
        return [
            'statut' => 'success',
            'data' => $users,
            'message' => 'Liste des utilisateurs récupérée avec succès.',
            'httpStatus' => 200
        ];
    }

    

}
