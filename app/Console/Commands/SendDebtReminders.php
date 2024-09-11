<?php

namespace App\Console\Commands;

use App\Services\Contracts\SmsServiceInterface;
use App\Models\Dette; // Importer le modèle Dette
use Illuminate\Console\Command;

class SendDebtReminders extends Command
{
    protected $signature = 'sms:send-debt-reminders';
    protected $description = 'Envoie des rappels de dettes par SMS à tous les clients ayant des dettes impayées';
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    public function handle()
    {
        // Appeler le service pour envoyer les rappels de dettes
        $dettes = Dette::with('paiements', 'client')->get();
        
        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant = $dette->montant - $totalPaiements;

            if ($montantRestant > 0) {
                $clientPhoneNumber = $dette->client->telephone;
                $clientName = $dette->client->surnom;

                // Envoyer le SMS
                $this->smsService->sendSmsToClient($clientPhoneNumber, $montantRestant, $clientName);

                // Afficher dans la console les informations du client et de la dette
                $this->info("Client: $clientName, Téléphone: $clientPhoneNumber, Montant Restant: $montantRestant FCFA");
            }
        }

        $this->info('Les rappels de dettes ont été envoyés.');
    }
}
