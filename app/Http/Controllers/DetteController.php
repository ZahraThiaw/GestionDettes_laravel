<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Resources\DetteResource;
use App\Traits\Response; // Assurez-vous d'importer le trait
use App\Enums\StatutResponse;
use App\Http\Resources\ClientResource;

class DetteController extends Controller
{
    use Response; // Utiliser le trait Response

    /**
     * Liste les dettes d'un client
     *
     * @param int $id
     *
     * @OA\Get(
     *     path="/clients/{id}/dettes",
     *     summary="Liste les dettes d'un client",
     *     tags={"Dette"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du client dont on veut récupérer les dettes",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du client avec ses dettes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client trouvé"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="client",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="telephone", type="string", example="0123456789"),
     *                     @OA\Property(property="adresse", type="string", example="123 Rue de la République")
     *                 ),
     *                 @OA\Property(
     *                     property="dettes",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-09-01"),
     *                         @OA\Property(property="montant", type="number", format="float", example=150.00),
     *                         @OA\Property(property="montantDu", type="number", format="float", example=100.00),
     *                         @OA\Property(property="montantRestant", type="number", format="float", example=50.00)
     *                     )
     *                 )
     *             )
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
    public function getClientDettes($id)
    {
        // Récupérer le client par son ID
        $client = Client::find($id);

        // Si le client n'existe pas, retourner une erreur
        if (!$client) {
            return $this->sendResponse([], StatutResponse::Echec, 'Client non trouvé.', 404);
        }

        // Récupérer les dettes du client sans les détails
        $dettes = $client->dettes()->get();

        // Préparer les données de réponse
        $responseData = [
            'client' => new ClientResource($client), // Informations du client
            'dettes' => $dettes->isEmpty() ? null : DetteResource::collection($dettes) // Dettes ou null
        ];

        // Retourner la réponse formatée avec les informations du client et ses dettes
        return $this->sendResponse(
            $responseData,
            StatutResponse::Success,
            'client trouvé',
            200
        );
    }

}
