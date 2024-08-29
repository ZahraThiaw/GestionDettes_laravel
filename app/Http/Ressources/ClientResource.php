<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'surnom' => $this->surnom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'user' => $this->when($this->user, new UserResource($this->user)),
        ];
    }
}
