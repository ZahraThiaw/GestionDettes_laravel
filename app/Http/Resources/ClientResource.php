<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request):array
    {
        return [
            'id' => $this->id,
            'surnom' => $this->surnom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'user' => $this->when($this->user, new UserResource($this->user)),
        ];
    }
}
