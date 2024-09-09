<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Resources\DetteResource;
//use App\Traits\Response; // Assurez-vous d'importer le trait
use App\Enums\StatutResponse;
use App\Http\Requests\StoreDetteRequest;
use App\Http\Requests\StorePaiementRequest;
use App\Http\Resources\ArticleDetteResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\PaiementResource;
use App\Models\Dette;
use App\Services\DetteServiceInterface;
use Illuminate\Http\Request;

class DetteController extends Controller
{
    //use Response; // Utiliser le trait Response


    protected $detteService;

    public function __construct(DetteServiceInterface $detteService)
    {
        $this->detteService = $detteService;
    }

    public function store(StoreDetteRequest $request)
    {
        $data = $request->validated();

        try {
            // Enregistrer la dette et ses détails
            $this->detteService->createDetteWithDetails($data); 

            $dette = Dette::latest()->first(); // Assurez-vous d'utiliser la méthode appropriée pour récupérer la dette enregistrée
            return [
                'statut' => 'Success',
                'data' => new DetteResource($dette),
                'message' => 'Dette enregistrée avec succès.',
                'httpStatus' => 201
            ];

        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Dette non enregistrée.',
                'httpStatus' => 411
            ];
        }
    }

    // Méthode pour lister toutes les dettes ou filtrer par statut
    public function index(Request $request)
    {
        try {
            $statut = $request->query('statut');
            
            if ($statut) {
                $dettes = $this->detteService->getDettesByStatus($statut);
            } else {
                $dettes = $this->detteService->getAllDettes();
            }

            // Vérifier si la collection est vide
            if ($dettes->isEmpty()) {
                return [
                    'statut' => 'Success',
                    'data' => [],
                    'message' => 'Aucune dette trouvée.',
                    'httpStatus' => 404
                ];
            }


            return [
                'statut' => 'Success',
                'data' => DetteResource::collection($dettes),
                'message' => 'Dettes récupérées avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => null,
                'message' => 'Erreur lors de la récupération des dettes : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function getClientDettes($id)
    {
        // Récupérer le client par son ID
        $client = Client::find($id);

        // Si le client n'existe pas, retourner une erreur
        if (!$client) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Client non trouvé.',
                'httpStatus' => 404
            ];
        }

        // Récupérer les dettes du client sans les détails
        $dettes = $client->dettes()->get();

        // Préparer les données de réponse
        $responseData = [
            'client' => new ClientResource($client), // Informations du client
            'dettes' => $dettes->isEmpty() ? null : DetteResource::collection($dettes) // Dettes ou null
        ];

        // Retourner la réponse formatée avec les informations du client et ses dettes
        return[
            'statut' => 'Success',
            'data' => $responseData,
            'message' => 'client trouvé',
            'httpStatus' => 200
        ];
    }


    public function show($id)
    {
        try {
            $dette = $this->detteService->getDetteById($id);

            if (!$dette) {
                return [
                    'statut' => 'Echec',
                    'data' => null,
                    'message' => 'Dette non trouvée.',
                    'httpStatus' => 404
                ];
            }

            return [
                'statut' => 'Success',
                'data' => new DetteResource($dette),
                'message' => 'Dette récupérée avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => null,
                'message' => 'Erreur lors de la récupération de la dette : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function listArticles($id)
    {
        try {
            // Charger uniquement la dette avec ses articles
            $dette = Dette::with('articles')->findOrFail($id);

            // Construire manuellement la réponse pour inclure seulement les champs nécessaires
            $response = [
                'id' => $dette->id,
                'date' => $dette->date,
                'montant' => $dette->montant,
                'client' => $dette->client->id,
                'articles' => ArticleDetteResource::collection($dette->articles) // Inclure uniquement les articles
            ];

            return [
                'statut' => 'Success',
                'data' => $response,
                'message' => 'Articles récupérés avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => null,
                'message' => 'Erreur lors de la récupération des articles : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function listPaiements($id)
    {
        try {
            // Charger uniquement la dette avec ses paiements
            $dette = Dette::with('paiements')->findOrFail($id);

            // Construire manuellement la réponse pour inclure seulement les champs nécessaires
            $response = [
                'id' => $dette->id,
                'date' => $dette->date,
                'montant' => $dette->montant,
                'client' => $dette->client->id,
                'paiements' => PaiementResource::collection($dette->paiements) // Inclure uniquement les paiements
            ];

            return [
                'statut' => 'Success',
                'data' => $response,
                'message' => 'Paiements récupérés avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => null,
                'message' => 'Erreur lors de la récupération des paiements : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

    public function addPaiement(StorePaiementRequest $request, $detteId)
    {
        $data = $request->validated();

        try {
            // Ajouter le paiement à la dette via le service
            $paiementDetails=$this->detteService->addPaiementToDette($detteId, $data);

            return [
                'statut' => 'Success',
                'data' => $paiementDetails,
                'message' => 'Paiement ajouté avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'message' => 'Erreur lors de l\'ajout du paiement : ' . $e->getMessage(),
                'httpStatus' => 500
            ];
        }
    }

}
