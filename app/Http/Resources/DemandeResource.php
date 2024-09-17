<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DemandeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date ? $this->date->format('Y-m-d') : null, // Vérification avant d'utiliser format()
            'status' => $this->status->label(), // Utilise l'énumération pour récupérer le libellé
            'client' => $this->client->surnom,  // Affiche le surnom du client
            'articles' => $this->articles->map(function($article) {
                return [
                    'libelle' => $article->libelle,
                    'quantite' => $article->pivot->qte,
                ];
            }),
        ];
    }
}
