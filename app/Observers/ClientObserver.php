<?php

namespace App\Observers;

use App\Events\ClientCreated;
use App\Events\LoyaltyCardGenerated;
use App\Models\Client;
use App\Services\LoyaltyCardService;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    protected $loyaltyCardService;

    public function __construct(LoyaltyCardService $loyaltyCardService)
    {
        $this->loyaltyCardService = $loyaltyCardService;
    }

    public function created(Client $client)
    {
        if (request()->hasFile('photo')) {
            $file = request()->file('photo');
            // Sauvegarder le fichier temporairement
            $tempPath = $file->store('temp');
            // Générer la carte de fidélité lors de la création d'un client
            $loyaltyCardPath = $this->loyaltyCardService->generateLoyaltyCard($client);
    
            // Émettre l'événement LoyaltyCardGenerated
            event(new ClientCreated($client, $tempPath, $loyaltyCardPath));
        }
        
    }
}