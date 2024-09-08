<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'montant' => $this->montant,
            'client' => new ClientResource($this->whenLoaded('client')),
            'articles' => ArticleDetteResource::collection($this->whenLoaded('articles')),
            'paiements' => PaiementResource::collection($this->whenLoaded('paiements')), // Inclure les paiements associés
        ];
    }
}
