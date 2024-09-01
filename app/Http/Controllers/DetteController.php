<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Resources\DetteResource;
use App\Traits\Response; // Assurez-vous d'importer le trait
use App\Enums\StatutResponse;
use App\Http\Resources\ClientResource;
use Illuminate\Http\JsonResponse;

class DetteController extends Controller
{
    use Response; // Utiliser le trait Response

    /**
     * Liste les dettes d'un client
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getClientDettes($id): JsonResponse
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
