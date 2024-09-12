<?php

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;
use App\Models\Dette;
use Illuminate\Support\Facades\Log;

class SmsDette
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendDebtReminders()
    {
        // Récupérer toutes les dettes avec leurs paiements et clients
        $dettes = Dette::with('paiements', 'client')->get();

        // Regrouper les dettes par client
        $clients = [];
        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant = $dette->montant - $totalPaiements;

            if ($montantRestant > 0) {
                $clientId = $dette->client->id;
                if (!isset($clients[$clientId])) {
                    $clients[$clientId] = [
                        'client' => $dette->client,
                        'montantRestant' => 0,
                    ];
                }
                $clients[$clientId]['montantRestant'] += $montantRestant;
            }
        }

        // Envoyer les rappels de dettes
        $sentReminders = [];
        foreach ($clients as $clientId => $clientData) {
            $client = $clientData['client'];
            $montantRestant = $clientData['montantRestant'];
            $clientPhoneNumber = $client->telephone;
            $clientName = $client->surnom;

            try {
                $this->smsService->sendSmsToClient($clientPhoneNumber, $montantRestant, $clientName);
                $sentReminders[] = [
                    'client' => $clientName,
                    'telephone' => $clientPhoneNumber,
                    'montant_restant' => $montantRestant,
                ];
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi du SMS au client $clientName ($clientPhoneNumber): " . $e->getMessage());
            }
        }

        return $sentReminders;
    }
}
