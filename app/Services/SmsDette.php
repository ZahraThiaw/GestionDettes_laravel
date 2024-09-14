<?php

namespace App\Services;

use App\Services\Contracts\SmsServiceInterface;
use App\Models\Dette;
use App\Models\Client;
use App\Notifications\DebtReminderNotification;
use Carbon\Carbon;

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
            $message = "Cher(e) {$client->surnom}, vous avez un montant restant de {$montantRestant} FCFA à régler. Merci de procéder au paiement.";
            $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant, $message));
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

            $message = "Cher(e) {$client->surnom}, vous avez un montant restant de {$montantRestant} FCFA à régler. Merci de procéder au paiement.";
            // Créer la notification de rappel
            $notification = new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant, $message);

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

                $message = "Cher(e) {$client->surnom}, vous avez un montant restant de {$montantRestant} FCFA à régler. Merci de procéder au paiement.";

                // Envoyer la notification uniquement si le client a des dettes non soldées
                $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant, $message));
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


    public function sendCustomMessageToClients(array $clientIds, string $customMessage)
    {
        $clientsWithDebt = [];
        $clientsWithoutDebt = [];
        $invalidClients = [];

        // Parcourir chaque client par identifiant
        foreach ($clientIds as $clientId) {
            $client = Client::find($clientId);

            if (!$client) {
                $invalidClients[] = $clientId;
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
                // Ajouter le client aux clients avec dettes
                $clientsWithDebt[] = [
                    'client_id' => $client->id,
                    'montant_restant' => $montantRestant,
                ];

                // Remplacer les variables dans le message personnalisé
                $message = str_replace(['{nom}', '{montant}'], [$client->surnom, $montantRestant], $customMessage);

                // Envoyer la notification par SMS
                $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant, $message));
            } else {
                // Ajouter aux clients sans dettes
                $clientsWithoutDebt[] = $client->id;
            }
        }

        // Retourner les résultats
        return [
            'clients_avec_dettes' => $clientsWithDebt,
            'clients_sans_dettes' => $clientsWithoutDebt,
            'clients_invalides' => $invalidClients,
        ];
    }


    public function sendOverdueDebtReminders()
    {
        // Récupérer toutes les dettes avec leurs paiements, clients et date d'échéance dépassée
        $currentDate = Carbon::now();
        $dettes = Dette::with('paiements', 'client')
            ->where('date_echeance', '<', $currentDate)
            ->get();

        $clients = [];
        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant = $dette->montant - $totalPaiements;

            // Vérifier si le montant restant est supérieur à zéro
            if ($montantRestant > 0) {
                $clientId = $dette->client->id;
                if (!isset($clients[$clientId])) {
                    $clients[$clientId] = [
                        'client' => $dette->client,
                        'montantRestant' => 0,
                        'date_echeance' => $dette->date_echeance,
                    ];
                }
                $clients[$clientId]['montantRestant'] += $montantRestant;
            }
        }

        // Envoyer la notification à chaque client dont la dette est en retard
        foreach ($clients as $clientData) {
            $client = $clientData['client'];
            $montantRestant = $clientData['montantRestant'];

            // Convertir date_echeance en objet Carbon si nécessaire
            $dateEcheance = Carbon::parse($clientData['date_echeance'])->format('d/m/Y');
            
            $message = "Cher(e) {$client->surnom}, votre échéance de paiement du {$dateEcheance} est dépassée. Il vous reste {$montantRestant} FCFA à régler. Merci de procéder au paiement.";

            // Envoyer la notification
            $client->notify(new DebtReminderNotification($client->surnom, $client->telephone, $montantRestant, $message));
        }

        return $clients;
    }

}
