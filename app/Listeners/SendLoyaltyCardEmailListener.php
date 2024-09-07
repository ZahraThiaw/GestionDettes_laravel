<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Jobs\SendLoyaltyCardEmail;
use App\Services\Contracts\ILoyaltyCardService;
use App\Services\LoyaltyCardService;
use Illuminate\Support\Facades\Log;

class SendLoyaltyCardEmailListener
{
    protected $loyaltyCardService;

     /* @param  \App\Services\LoyaltyCardService  $loyaltyCardService
     * @return void
     */
    public function __construct(ILoyaltyCardService $loyaltyCardService)
    {
        $this->loyaltyCardService = $loyaltyCardService;
    }

    public function handle(UserCreated $event)
    {
        // Assurez-vous que le client existe pour l'utilisateur
        $client = $event->user->client; // Récupérer le client associé à l'utilisateur

        if ($client) {
            // Générer la carte de fidélité et envoyer l'e-mail
            $loyaltyCardPath = $this->loyaltyCardService->generateLoyaltyCard($client);
            SendLoyaltyCardEmail::dispatch($client, $loyaltyCardPath);
        } else {
            // Gérer le cas où le client n'existe pas
            Log::warning('Le client associé à l\'utilisateur n\'existe pas.');
        }
    }
}