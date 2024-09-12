<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
//use App\Traits\Response;
use App\Enums\StatutResponse;
use App\Http\Requests\UpdateStockRequest;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    //use Response;

    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index(Request $request)
    {
        // Récupérer le paramètre de disponibilité
        $disponible = $request->query('disponible');

        // Vérifier si le paramètre de disponibilité est valide
        if ($disponible === 'oui' || $disponible === 'non') {
            // Filtrer les articles en fonction du paramètre de disponibilité
            $articles = $this->articleService->findByEtat($disponible);

            if ($articles->isEmpty()) {
                return [
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'Aucun article trouvé pour cet état.',
                    'httpStatus' => 404
                ];
            }

            return [
                'statut' => 'Success',
                'data' => ArticleResource::collection($articles),
                'message' => 'Liste des articles filtrée par disponibilité récupérée avec succès.',
                'httpStatus' => 200
            ];
        }

        // Si le paramètre de disponibilité n'est pas fourni, ou n'est pas valide
        $articles = $this->articleService->all();

        if ($articles->isEmpty()) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucun article trouvé.',
                'httpStatus' => 404
            ];
        }

        return [
            'statut' => 'Success',
            'data' => ArticleResource::collection($articles),
            'message' => 'Liste des articles récupérée avec succès.',
            'httpStatus' => 200
        ];
    }

    // public function filterByLibelle(Request $request)
    // {
    //     $libelle = $request->input('libelle');
    //     $article = $this->articleService->findByLibelle($libelle);

    //     if (!$article) {
    //         return [
    //             'statut' => 'Echec',
    //             'data' => [],
    //             'message' => 'Aucun article trouvé avec ce libellé',
    //             'httpStatus' => 404
    //         ];
    //     }

    
    //     return [
    //         'statut' => 'Success',
    //         'data' => new ArticleResource($article),
    //         'message' => 'Article récupéré avec succès.',
    //         'httpStatus' => 200
    //     ];
    // }


    public function filterByLibelle(Request $request)
    {
        $libelle = $request->input('libelle');

        // Vérification si le champ libelle est fourni
        if (empty($libelle)) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Le champ libellé est requis',
                'httpStatus' => 400
            ];
        }

        // Rechercher l'article en appelant le service
        $article = $this->articleService->findByLibelle($libelle);

        // Vérifier si un article est trouvé
        if (!$article) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Aucun article trouvé avec ce libellé',
                'httpStatus' => 404
            ];
        }

        // Article trouvé, retour de la réponse avec succès
        return [
            'statut' => 'Success',
            'data' => new ArticleResource($article),
            'message' => 'Article récupéré avec succès.',
            'httpStatus' => 200
        ];
    }


    public function show($id)
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Article non trouvé',
                'httpStatus' => 404
            ];
        }



        return [
            'statut' => 'Success',
            'data' => new ArticleResource($article),
            'message' => 'Article récupéré avec succès.',
            'httpStatus' => 200
        ];
    }

    public function store(StoreArticleRequest $request)
    {
        $article = $this->articleService->create($request->validated());
        
        return [
            'statut' => 'Success',
            'data' => new ArticleResource($article),
            'message' => 'Article créé avec succès',
            'httpStatus' => 200
        ];
    }
    
    public function update(UpdateArticleRequest $request, $id)
    {
        $article = $this->articleService->update($id, $request->validated());

        return [
            'statut' => 'Success',
            'data' => new ArticleResource($article),
            'message' => 'Article mis à jour avec succès',
            'httpStatus' => 200
        ];
    }


    // public function updateStock(Request $request)
    // {
    //     $updatedArticles = $this->articleService->updateStock($request->all());

    //     return [
    //         'statut' => 'Success',
    //         'data' => ArticleResource::collection($updatedArticles),
    //         'message' => 'Mise à jour du stock effectuée avec succès',
    //         'httpStatus' => 200
    //     ];
    // }


    // Mise à jour du stock
    public function updateStock(UpdateStockRequest $request)
    {
        // Récupération de la liste des articles depuis la requête validée
        $articlesData = $request->input('articles');

        // Mise à jour du stock via le service
        $stockUpdateResult = $this->articleService->updateStock($articlesData);

        // Réponse formatée
        return response()->json([
            'statut' => 'Success',
            'data' => [
                'success' => ArticleResource::collection($stockUpdateResult['success']),
                'error' => $stockUpdateResult['error'], // Liste des ID non trouvés
            ],
            'httpStatus' => 200
        ], 200);
    }

    public function destroy($id)
    {
        try {
            $article = $this->articleService->delete($id);

            if (!$article) {
                return response()->json([
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'L\'article n\'existe pas ou a déjà été supprimé.',
                    'httpStatus' => 404
                ], 404);
            }

            return response()->json([
                'statut' => 'Success',
                'data' => null, // Pas besoin de renvoyer les données de l'article après suppression
                'message' => 'Article supprimé avec succès.',
                'httpStatus' => 200
            ], 200);

        } catch (\Exception $e) {
            // Gestion des exceptions générales
            return response()->json([
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Une erreur est survenue lors de la suppression de l\'article.',
                'httpStatus' => 500
            ], 500);
        }
    }
    public function restore($id)
    {
        try {
            $article = $this->articleService->restore($id);

            if (!$article) {
                // Si l'article n'a pas pu être restauré (par exemple, il n'existe pas dans la corbeille)
                return [
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'L\'article n\'existe pas ou n\'a pas été supprimé.',
                    'httpStatus' => 404
                ];
            }

            return [
                'statut' => 'Success',
                'data' => new ArticleResource($article),
                'message' => 'Article restauré avec succès.',
                'httpStatus' => 200
            ];

        } catch (\Exception $e) {
            // Gestion des exceptions générales
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Une erreur est survenue lors de la restauration de l\'article.',
                'httpStatus' => 500
            ];
        }
    }


}
