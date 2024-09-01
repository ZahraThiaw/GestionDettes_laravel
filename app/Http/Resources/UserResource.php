<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'login' => $this->login,
            'role' => $this->role,
        ];
    }
}
