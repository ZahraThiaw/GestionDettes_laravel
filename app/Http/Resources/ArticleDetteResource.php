<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleDetteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'articleId' => $this->id,
            'qteVente' => $this->pivot->qteVente,
            'prixVente' => $this->pivot->prixVente,
        ];
    }
}
