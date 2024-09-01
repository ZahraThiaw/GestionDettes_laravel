<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'nom',
            'prenom',
            'login',
            'password',
            'role_id', // Assurez-vous que 'role_id' est inclus
            'photo'
        ];
    }
}
