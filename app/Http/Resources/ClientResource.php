<?php

// namespace App\Http\Resources;

// use Illuminate\Http\Resources\Json\JsonResource;

// class ClientResource extends JsonResource
// {
//     public function toArray($request):array
//     {
//         return [
//             'id' => $this->id,
//             'surnom' => $this->surnom,
//             'telephone' => $this->telephone,
//             'adresse' => $this->adresse,
//             'user' => $this->when($this->user, new UserResource($this->user)),
//         ];
//     }
// }


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'surnom' => $this->surnom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'nom' => $this->user->nom,
                    'prenom' => $this->user->prenom,
                    'login' => $this->user->login,
                    'role_id' => $this->user->role_id,
                    'photo' => $this->user->photo,
                ];
            }),
        ];
    }
}
