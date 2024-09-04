<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\StatutResponse;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Exécuter la requête et capturer la réponse brute
        $response = $next($request);

        // Vérifier si la réponse est déjà une instance de JsonResponse
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }

        // Si la réponse n'est pas JsonResponse, on suppose que c'est un tableau brut
        $data = $response->original ?? [];

        // Déterminer le statut (succès ou échec) en fonction des données fournies
        $statut = isset($data['statut']) 
            ? $data['statut'] 
            : StatutResponse::Success;

        // Retourner la réponse formatée en JSON
        return response()->json([
            'statut' => $statut->value,
            'data' => $data['data'] ?? $data,  // Si 'data' existe dans la réponse, on l'utilise, sinon c'est les données brutes
            'message' => $data['message'] ?? '',
        ], $response->status() ?? 200); // Utiliser le statut HTTP de la réponse si défini
    }
}

