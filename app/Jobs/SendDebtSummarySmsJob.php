<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDebtSummarySmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;

    /**
     * Crée une nouvelle instance du job.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Exécute le job.
     *
     * @return void
     */
    public function handle(SmsService $smsService)
    {
        // Parcourir toutes les dettes du client
        $totalMontantRestant = 0;

        foreach ($this->client->dettes as $dette) {
            // Calculer la somme totale des paiements effectués pour chaque dette
            $totalPaiements = $dette->paiements->sum('montant');

            // Calculer le montant restant pour la dette (dette initiale - total des paiements)
            $montantRestant = $dette->montant - $totalPaiements;

            // Ajouter le montant restant à la somme totale
            $totalMontantRestant += $montantRestant;
        }

        // Vérifier si le montant restant est supérieur à 0
        if ($totalMontantRestant > 0) {
            // Message à envoyer
            $message = "Bonjour {$this->client->nom}, il vous reste une dette totale de {$totalMontantRestant} FCFA. Merci de régulariser votre situation.";

            // Utiliser le service SmsService pour envoyer le SMS
            $smsService->sendSms($this->client->telephone, $message);
        }
    }
}

