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
        $dettes = Dette::with('paiements', 'client')->get();
        $sentReminders = [];

        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant = $dette->montant - $totalPaiements;

            if ($montantRestant > 0) {
                $clientPhoneNumber = $dette->client->telephone;
                $clientName = $dette->client->surnom;

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
        }

        return $sentReminders;
    }
}
