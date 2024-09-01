<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\Response;
use App\Enums\StatutResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use Response;

    public function index(Request $request)
    {
        $query = Article::query()->whereNull('deleted_at'); // Exclure les articles supprimés

        // Vérifier si le filtre 'disponible' est présent dans la requête
        if ($request->has('disponible')) {
            $disponible = $request->query('disponible');

            if ($disponible === 'oui') {
                // Articles disponibles (qteStock > 0)
                $query->where('qteStock', '>', 0);
            } elseif ($disponible === 'non') {
                // Articles non disponibles (qteStock = 0)
                $query->where('qteStock', '=', 0);
            }
        }

        // Pagination des articles filtrés
        $articles = $query->paginate(10); // 10 articles par page

        if ($articles->isEmpty()) {
            return $this->sendResponse([], StatutResponse::Echec, 'Aucun article trouvé.', 404);
        }

        return $this->sendResponse(ArticleResource::collection($articles), StatutResponse::Success, 'Liste des articles récupérée avec succès.', 200);
    }

    //Filtre article par libelle
    public function filterByLibelle(Request $request)
    {
        $query = Article::query()->whereNull('deleted_at'); // Exclure les articles supprimés

        // Vérifier si le libellé est présent dans le corps de la requête
        if ($request->has('libelle')) {
            $libelle = $request->input('libelle');
            $article = $query->where('libelle', 'LIKE', "%{$libelle}%")->first(); // Utiliser LIKE pour une recherche partielle et récupérer le premier résultat

            if (!$article) {
                return $this->sendResponse([], StatutResponse::Echec, 'Aucun article trouvé avec ce libellé.', 404);
            }

            return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article récupéré avec succès.', 200);
        }

        return $this->sendResponse([], StatutResponse::Echec, 'Libellé non fourni.', 404);
    }

    // Afficher un article
    public function show($id)
    {
        $article = Article::where('id', $id)->whereNull('deleted_at')->first();

        if (!$article) {
            return $this->sendResponse([], StatutResponse::Echec, 'Article non trouvé.', 404);
        }

        return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article récupéré avec succès.', 200);
    }


    // Créer un nouvel article
    public function store(StoreArticleRequest $request)
    {
        $article = Article::create($request->validated());
        return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article créé avec succès', 201);
    }

    public function update(UpdateArticleRequest $request, $id): JsonResponse
    {
        try {
            // Récupérer l'article par son ID
            $article = Article::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Retourner un message d'erreur si l'article n'existe pas
            return $this->sendResponse([], StatutResponse::Echec, 'Article non trouvé.', 404);
        }
    
        // Obtenir les données validées
        $data = $request->validated();
    
        // Vérifier si au moins une donnée à mettre à jour est fournie
        if (empty($data)) {
            return $this->sendResponse([], StatutResponse::Echec, 'Aucune mise à jour effectuée car aucune valeur n\'a été fournie.', 400);
        }
    
        // Mettre à jour la quantité en stock en ajoutant la nouvelle valeur à l'existante
        if (isset($data['qteStock'])) {
            $article->qteStock += $data['qteStock'];
            unset($data['qteStock']); // Retirer la clé pour éviter une mise à jour non souhaitée
        }
    
        // Mettre à jour l'article avec les données restantes
        $article->update($data);
    
        // Retourner la réponse avec les données mises à jour
        return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article mis à jour avec succès', 200);
    }    

    public function updateStock(Request $request): JsonResponse
    {
        // Récupérer le corps de la requête qui contient la liste des articles
        $articlesData = $request->all();

        // Initialiser les listes de succès et d'erreurs
        $updatedArticles = [];
        $notFoundArticles = [];
        $invalidStock = [];

        // Parcourir chaque article fourni dans la requête
        foreach ($articlesData as $articleData) {
            // Vérifier si l'ID et la quantité sont présents
            if (isset($articleData['id'], $articleData['qteStock'])) {
                // Vérifier si la qteStock est supérieure ou égale à 1
                if ($articleData['qteStock'] < 1) {
                    // Ajouter à la liste des stocks invalides
                    $invalidStock[] = $articleData['id'];
                    continue; // Passer à l'élément suivant
                }

                // Trouver l'article par ID
                $article = Article::find($articleData['id']);

                // Si l'article existe, on met à jour la quantité en stock
                if ($article) {
                    $article->qteStock += $articleData['qteStock']; // Ajouter la quantité
                    $article->save(); // Sauvegarder les changements
                    $updatedArticles[] = new ArticleResource($article); // Ajouter à la liste des succès
                } else {
                    // Ajouter à la liste des articles non trouvés
                    $notFoundArticles[] = $articleData['id'];
                }
            } else {
                // Si l'ID ou qteStock est manquant, ajouter à la liste des erreurs
                $notFoundArticles[] = $articleData['id'] ?? 'ID manquant';
            }
        }

        // Retourner la réponse avec les articles mis à jour, les erreurs et les stocks invalides
        return $this->sendResponse(
            [
                'success' => $updatedArticles,
                'error' => $notFoundArticles,
                'invalidStock' => $invalidStock
            ],
            StatutResponse::Success,
            'Mise à jour du stock effectuée avec succès.',
            201
        );
    }

    // Supprimer un article (Soft Delete)
    public function destroy($id)
    {
        try {
            // Récupérer l'article par son ID
            $article = Article::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Retourner un message d'erreur si l'article n'existe pas
            return $this->sendResponse([], StatutResponse::Echec, 'L\'article avec l\'ID spécifié n\'existe pas.', 404);
        }

        // Soft delete
        $article->delete();

        return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article supprimé avec succès', 200);
    }

    // Restaurer un article supprimé
    public function restore($id)
    {
        try {
            // Récupérer l'article soft supprimé par son ID
            $article = Article::onlyTrashed()->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Retourner un message d'erreur si l'article n'existe pas parmi les articles supprimés
            return $this->sendResponse([], StatutResponse::Echec, 'L\'article avec l\'ID spécifié n\'existe pas ou n\'a pas été supprimé.', 404);
        }

        // Restaurer l'article soft supprimé
        $article->restore();

        return $this->sendResponse(new ArticleResource($article), StatutResponse::Success, 'Article restauré avec succès', 200);
    }

}
