<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     security={{"bearerAuth":{}}},
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 @OA\Property(property="role_id", type="integer", example=1),
     *                 @OA\Property(property="photo", type="string", format="binary"),
     *                 required={"name", "email", "password", "role_id"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role_id", type="integer", example=1),
     *                 @OA\Property(property="photo", type="string", example="photo.jpg")
     *             ),
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating user",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la création de l'utilisateur: Error message")
     *         )
     *     )
     * )
     */
    // public function store(UserRequest $request)
    // {
    //     // Démarrer une transaction pour garantir l'intégrité des données
    //     DB::beginTransaction();

    //     try {
    //         // Obtenir les données validées de la requête
    //         $data = $request->validated();

    //         // Gérer le téléchargement de la photo
    //         if ($request->hasFile('photo')) {
    //             $photoPath = $request->file('photo')->store('public/photos');
    //             $data['photo'] = basename($photoPath); // Stocker seulement le nom du fichier
    //         }

    //         // Créer l'utilisateur avec les données validées, y compris le role_id
    //         $user = User::create($data);

    //         // Confirmer la transaction
    //         DB::commit();

    //         return response()->json([
    //             'statut' => 'success',
    //             'data' => $user,
    //             'message' => 'Utilisateur créé avec succès.'
    //         ], 201);

    //     } catch (\Exception $e) {
    //         // Annuler la transaction en cas d'erreur
    //         DB::rollBack();

    //         return response()->json([
    //             'statut' => 'error',
    //             'message' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(UserRequest $request)
{
    // Démarrer une transaction pour garantir l'intégrité des données
    DB::beginTransaction();

    try {
        // Obtenir les données validées de la requête
        $data = $request->validated();

        // Gérer le téléchargement de la photo
        if ($request->hasFile('photo')) {
            // Lire le fichier photo
            $photo = $request->file('photo');
            
            // Convertir le fichier photo en base64
            $photoData = file_get_contents($photo->getRealPath());
            $base64Photo = base64_encode($photoData);

            // Préfixer les données base64 pour indiquer le type d'image
            $data['photo'] = 'data:image/' . $photo->getClientOriginalExtension() . ';base64,' . $base64Photo;
        }

        // Créer l'utilisateur avec les données validées, y compris le role_id
        $user = User::create($data);

        // Confirmer la transaction
        DB::commit();

        return response()->json([
            'statut' => 'success',
            'data' => $user,
            'message' => 'Utilisateur créé avec succès.'
        ], 201);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'statut' => 'error',
            'message' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()
        ], 500);
    }
}


     /**
     * @OA\Get(
     *     path="/users",
     *     summary="Retrieve a list of users",
     *     security={{"bearerAuth":{}}},
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filter users by active status (oui or non)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role_id", type="integer", example=1),
     *                     @OA\Property(property="photo", type="string", example="photo.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Liste des utilisateurs récupérée avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid role specified",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Le rôle spécifié est invalide.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No users found",
     *         @OA\JsonContent(
     *             @OA\Property(property="statut", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Aucun utilisateur trouvé.")
     *         )
     *     )
     * )
     */
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
                return response()->json([
                    'statut' => 'error',
                    'data' => null,
                    'message' => 'Le rôle spécifié est invalide.',
                ], 400);
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
            return response()->json([
                'statut' => 'error',
                'data' => null,
                'message' => 'Aucun utilisateur trouvé',
            ], 404);
        }

        // Retourner la réponse JSON avec les utilisateurs trouvés
        return response()->json([
            'statut' => 'success',
            'data' => $users,
            'message' => 'Liste des utilisateurs récupérée avec succès.',
        ], 200);
    }

}
