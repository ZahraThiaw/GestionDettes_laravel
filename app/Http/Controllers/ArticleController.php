<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
//use App\Traits\Response;
use App\Enums\StatutResponse;
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

/**
     * @OA\Get(
     *     path="/articles",
     *     summary="Liste des articles",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="disponible",
     *         in="query",
     *         description="Filtrer les articles disponibles ou non",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des articles récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liste des articles récupérée avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="libelle", type="string", example="Article Example"),
     *                     @OA\Property(property="qteStock", type="integer", example=100)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun article trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Aucun article trouvé.")
     *         )
     *     )
     * )
     */
    // public function index(Request $request)
    // {
    //     $query = Article::query()->whereNull('deleted_at'); // Exclure les articles supprimés

    //     // Vérifier si le filtre 'disponible' est présent dans la requête
    //     if ($request->has('disponible')) {
    //         $disponible = $request->query('disponible');

    //         if ($disponible === 'oui') {
    //             // Articles disponibles (qteStock > 0)
    //             $query->where('qteStock', '>', 0);
    //         } elseif ($disponible === 'non') {
    //             // Articles non disponibles (qteStock = 0)
    //             $query->where('qteStock', '=', 0);
    //         }
    //     }


    //     // Pagination des articles filtrés
    //     $articles = $query->paginate(10); // 10 articles par page

    //     if ($articles->isEmpty()) {
    //         return $this->sendResponse([], StatutResponse::Echec, 'Aucun article trouvé.', 404);
    //     }

    //     return $this->sendResponse(ArticleResource::collection($articles), StatutResponse::Success, 'Liste des articles récupérée avec succès.', 200);
    // }

    public function index(Request $request)
    {
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
    public function findByDisponibilite(Request $request)
    {
        $disponible = $request->query('disponible');
        //\Log::info('Paramètre disponible: ' . $disponible); // Ligne de débogage

        if ($disponible === 'oui' || $disponible === 'non') {
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
        
        return [
            'statut' => 'Echec',
            'data' => [],
            'message' => 'Invalid availability status.',
            'httpStatus' => 400
        ];
    }


    /**
     * @OA\Get(
     *     path="/articles/libelle",
     *     summary="Filtrer un article par libellé",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="libelle",
     *         in="query",
     *         description="Libellé de l'article",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Article récupéré avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="libelle", type="string", example="Article Example"),
     *                 @OA\Property(property="qteStock", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun article trouvé avec ce libellé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Aucun article trouvé avec ce libellé.")
     *         )
     *     )
     * )
     */


    //Filtre article par libelle
    // public function filterByLibelle(Request $request)
    // {
    //     $query = Article::query()->whereNull('deleted_at'); // Exclure les articles supprimés

    //     // Vérifier si le libellé est présent dans le corps de la requête
    //     if ($request->has('libelle')) {
    //         $libelle = $request->input('libelle');
    //         $article = $query->where('libelle', 'LIKE', "%{$libelle}%")->first(); // Utiliser LIKE pour une recherche partielle et récupérer le premier résultat

    //         if (!$article) {
    //             return $this->sendResponse([], StatutResponse::Echec, 'Aucun article trouvé avec ce libellé.', 404);
    //         }

    //         return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article récupéré avec succès.', 200);
    //     }

    //     return $this->sendResponse([], StatutResponse::Echec, 'Libellé non fourni.', 404);
    // }


    public function filterByLibelle(Request $request)
    {
        $libelle = $request->input('libelle');
        $article = $this->articleService->findByLibelle($libelle);

        if (!$article) {
            return $this->sendResponse([], StatutResponse::Echec, 'Aucun article trouvé avec ce libellé.', 404);
        }

        return $this->sendResponse($article, StatutResponse::Success, 'Article récupéré avec succès.', 200);
    }


    /**
     * @OA\Get(
     *     path="/articles/{id}",
     *     summary="Afficher un article",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Article récupéré avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="libelle", type="string", example="Article Example"),
     *                 @OA\Property(property="qteStock", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Article non trouvé.")
     *         )
     *     )
     * )
     */

    // Afficher un article
    // public function show($id)
    // {
    //     $article = Article::where('id', $id)->whereNull('deleted_at')->first();

    //     if (!$article) {
    //         return $this->sendResponse([], StatutResponse::Echec, 'Article non trouvé.', 404);
    //     }

    //     return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article récupéré avec succès.', 200);
    // }

    public function show($id)
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->sendResponse([], StatutResponse::Echec, 'Article non trouvé.', 404);
        }

        return $this->sendResponse($article, StatutResponse::Success, 'Article récupéré avec succès.', 200);
    }

    /**
     * @OA\Post(
     *     path="/articles",
     *     summary="Créer un nouvel article",
     *     tags={"Article"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="libelle", type="string", example="Article Example"),
     *                 @OA\Property(property="qteStock", type="integer", example=100),
     *                 required={"libelle", "qteStock"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Article créé avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="libelle", type="string", example="Article Example"),
     *                 @OA\Property(property="qteStock", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Requête invalide.")
     *         )
     *     )
     * )
     */

    // Créer un nouvel article
    // public function store(StoreArticleRequest $request)
    // {
    //     $article = Article::create($request->validated());
    //     return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article créé avec succès', 201);
    // }

    public function store(StoreArticleRequest $request)
    {
        $article = $this->articleService->create($request->validated());
        return $this->sendResponse($article, StatutResponse::Success, 'Article créé avec succès', 201);
    }
    /**
     * @OA\Put(
     *     path="/articles/{id}",
     *     summary="Mettre à jour un article",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="libelle", type="string", example="Article Example Updated"),
     *                 @OA\Property(property="qteStock", type="integer", example=200),
     *                 required={"libelle", "qteStock"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Article mis à jour avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="libelle", type="string", example="Article Example Updated"),
     *                 @OA\Property(property="qteStock", type="integer", example=200)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Article non trouvé.")
     *         )
     *     )
     * )
     */
    // public function update(UpdateArticleRequest $request, $id): JsonResponse
    // {
    //     try {
    //         // Récupérer l'article par son ID
    //         $article = Article::findOrFail($id);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         // Retourner un message d'erreur si l'article n'existe pas
    //         return $this->sendResponse([], StatutResponse::Echec, 'Article non trouvé.', 404);
    //     }
    
    //     // Obtenir les données validées
    //     $data = $request->validated();
    
    //     // Vérifier si au moins une donnée à mettre à jour est fournie
    //     if (empty($data)) {
    //         return $this->sendResponse([], StatutResponse::Echec, 'Aucune mise à jour effectuée car aucune valeur n\'a été fournie.', 400);
    //     }
    
    //     // Mettre à jour la quantité en stock en ajoutant la nouvelle valeur à l'existante
    //     if (isset($data['qteStock'])) {
    //         $article->qteStock += $data['qteStock'];
    //         unset($data['qteStock']); // Retirer la clé pour éviter une mise à jour non souhaitée
    //     }
    
    //     // Mettre à jour l'article avec les données restantes
    //     $article->update($data);
    
    //     // Retourner la réponse avec les données mises à jour
    //     return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article mis à jour avec succès', 200);
    // }    

    public function update(UpdateArticleRequest $request, $id): JsonResponse
    {
        $article = $this->articleService->update($id, $request->validated());
        return $this->sendResponse($article, StatutResponse::Success, 'Article mis à jour avec succès', 200);
    }

    /**
     * @OA\Put(
     *     path="/articles/update-stock",
     *     summary="Mettre à jour le stock des articles",
     *     tags={"Article"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="qteStock", type="integer", example=10),
     *                     required={"id", "qteStock"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mise à jour du stock effectuée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="libelle", type="string", example="Article Example"),
     *                     @OA\Property(property="qteStock", type="integer", example=100),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *             @OA\Property(
     *                 property="invalidStock",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             ),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Mise à jour du stock effectuée avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Requête invalide.")
     *         )
     *     )
     * )
 */
    // public function updateStock(Request $request): JsonResponse
    // {
    //     // Récupérer le corps de la requête qui contient la liste des articles
    //     $articlesData = $request->all();

    //     // Initialiser les listes de succès et d'erreurs
    //     $updatedArticles = [];
    //     $notFoundArticles = [];
    //     $invalidStock = [];

    //     // Parcourir chaque article fourni dans la requête
    //     foreach ($articlesData as $articleData) {
    //         // Vérifier si l'ID et la quantité sont présents
    //         if (isset($articleData['id'], $articleData['qteStock'])) {
    //             // Vérifier si la qteStock est supérieure ou égale à 1
    //             if ($articleData['qteStock'] < 1) {
    //                 // Ajouter à la liste des stocks invalides
    //                 $invalidStock[] = $articleData['id'];
    //                 continue; // Passer à l'élément suivant
    //             }

    //             // Trouver l'article par ID
    //             $article = Article::find($articleData['id']);

    //             // Si l'article existe, on met à jour la quantité en stock
    //             if ($article) {
    //                 $article->qteStock += $articleData['qteStock']; // Ajouter la quantité
    //                 $article->save(); // Sauvegarder les changements
    //                 $updatedArticles[] = new ArticleResource($article); // Ajouter à la liste des succès
    //             } else {
    //                 // Ajouter à la liste des articles non trouvés
    //                 $notFoundArticles[] = $articleData['id'];
    //             }
    //         } else {
    //             // Si l'ID ou qteStock est manquant, ajouter à la liste des erreurs
    //             $notFoundArticles[] = $articleData['id'] ?? 'ID manquant';
    //         }
    //     }

    //     // Retourner la réponse avec les articles mis à jour, les erreurs et les stocks invalides
    //     return $this->sendResponse(
    //         [
    //             'success' => $updatedArticles,
    //             'error' => $notFoundArticles,
    //             'invalidStock' => $invalidStock
    //         ],
    //         StatutResponse::Success,
    //         'Mise à jour du stock effectuée avec succès.',
    //         201
    //     );
    // }

    // public function updateStock(Request $request): JsonResponse
    // {
    //     $articlesData = $request->all();
    //     $result = $this->articleService->updateStock($articlesData);

    //     return $this->sendResponse($result, StatutResponse::Success, 'Mise à jour du stock effectuée avec succès.', 201);
    // }

        public function updateStock(Request $request): JsonResponse
        {
            $updatedArticles = $this->articleService->updateStock($request->all());
            return $this->sendResponse($updatedArticles, StatutResponse::Success, 'Mise à jour du stock effectuée avec succès.', 201);
        }

/**
     * @OA\Delete(
     *     path="/articles/{id}",
     *     summary="Supprimer un article",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Article supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Article non trouvé.")
     *         )
     *     )
     * )
     */

    // Supprimer un article (Soft Delete)
    // public function destroy($id)
    // {
    //     try {
    //         // Récupérer l'article par son ID
    //         $article = Article::findOrFail($id);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         // Retourner un message d'erreur si l'article n'existe pas
    //         return $this->sendResponse([], StatutResponse::Echec, 'L\'article avec l\'ID spécifié n\'existe pas.', 404);
    //     }

    //     // Soft delete
    //     $article->delete();

    //     return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article supprimé avec succès', 200);
    // }

    public function destroy($id)
    {
        $article = $this->articleService->delete($id);
        return $this->sendResponse($article, StatutResponse::Success, 'Article supprimé avec succès', 200);
    }

    /**
     * @OA\Put(
     *     path="/articles/{id}/restore",
     *     summary="Restaurer un article soft supprimé",
     *     tags={"Article"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'article à restaurer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article restauré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Article restauré avec succès."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="libelle", type="string", example="Article Example"),
     *                 @OA\Property(property="qteStock", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="L'article avec l'ID spécifié n'existe pas ou n'a pas été supprimé.")
     *         )
     *     )
     * )
     */
    // Restaurer un article supprimé
    // public function restore($id)
    // {
    //     try {
    //         // Récupérer l'article soft supprimé par son ID
    //         $article = Article::onlyTrashed()->findOrFail($id);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         // Retourner un message d'erreur si l'article n'existe pas parmi les articles supprimés
    //         return $this->sendResponse([], StatutResponse::Echec, 'L\'article avec l\'ID spécifié n\'existe pas ou n\'a pas été supprimé.', 404);
    //     }

    //     // Restaurer l'article soft supprimé
    //     $article->restore();

    //     return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article restauré avec succès', 200);
    // }

    public function restore($id)
    {
        $article = $this->articleService->restore($id);
        return $this->sendResponse($article, StatutResponse::Success, 'Article restauré avec succès', 200);
    }

}
