<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'montant' => $this->montant,
            'date' => $this->date, // Ajout de la date du paiement
            'dette_id' => $this->dette_id, // Relation avec la dette
        ];
    }
}
