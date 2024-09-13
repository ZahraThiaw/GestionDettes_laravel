<?php

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;
use App\Models\Dette;
use App\Models\Client;
use App\Notifications\DebtReminderNotification;

class SmsDette
{
    public function sendDebtReminders()
    {
        // Récupérer toutes les dettes avec leurs paiements et clients
        $dettes = Dette::with('paiements', 'client')->get();

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

        // Envoyer la notification à chaque client
        foreach ($clients as $clientData) {
            $client = $clientData['client'];
            $montantRestant = $clientData['montantRestant'];
            $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant));
        }

        return $clients;
    }

    public function sendDebtReminderToOneClient(Client $client)
    {
        // Récupérer les dettes du client
        $dettes = Dette::where('client_id', $client->id)->with('paiements')->get();
        $montantRestant = 0;

        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant += $dette->montant - $totalPaiements;
        }

        if ($montantRestant > 0) {
            // Créer la notification de rappel
            $notification = new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant);

            // Envoyer la notification au client
            $client->notify($notification);

            // Retourner la notification envoyée
            return $notification;
        }

        // Retourner null si le client n'a pas de dettes non soldées
        return null;
    }

    public function sendDebtRemindersToClients($clients)
    {
        $clientIdsValidWithDebt = [];
        $clientIdsValidWithoutDebt = [];
        $clientIdsInvalid = [];

        foreach ($clients as $client) {
            if (!$client) {
                $clientIdsInvalid[] = $client->id ;
                continue;
            }

            // Récupérer les dettes du client
            $dettes = Dette::where('client_id', $client->id)->with('paiements')->get();
            $montantRestant = 0;

            foreach ($dettes as $dette) {
                $totalPaiements = $dette->paiements->sum('montant');
                $montantRestant += $dette->montant - $totalPaiements;
            }

            if ($montantRestant > 0) {
                // Ajouter aux clients avec dettes non soldées
                $clientIdsValidWithDebt[] = [
                    'client_id' => $client->id,
                    'montant_restant' => $montantRestant,
                ];

                // Envoyer la notification uniquement si le client a des dettes non soldées
                $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant));
            } else {
                // Ajouter aux clients valides mais sans dettes non soldées
                $clientIdsValidWithoutDebt[] = $client->id;
            }
        }

        return [
            'clients_avec_dettes' => $clientIdsValidWithDebt,
            'clients_sans_dettes' => $clientIdsValidWithoutDebt,
            'clients_invalides' => $clientIdsInvalid,
        ];
    }
}
