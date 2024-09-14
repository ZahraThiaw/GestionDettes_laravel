<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsDette;

class SendDebtReminderNotification extends Command
{
    // Signature de la commande
    protected $signature = 'notification:send-overdue-debt-reminders';

    // Description de la commande
    protected $description = 'Envoyer des notifications de rappel de paiement aux clients dont la date d\'échéance est dépassée.';

    // Injection du service SmsDette
    protected $smsDette;

    public function __construct(SmsDette $smsDette)
    {
        parent::__construct();
        $this->smsDette = $smsDette;
    }

    // Logique à exécuter lorsque la commande est appelée
    public function handle()
    {
        $this->info('Envoi des notifications de rappel pour les dettes échues...');

        // Appel à la méthode sendOverdueDebtReminders() du service SmsDette
        $clientsNotified = $this->smsDette->sendOverdueDebtReminders();

        if (empty($clientsNotified)) {
            $this->info('Aucun client avec des dettes échues.');
        } else {
            $this->info(count($clientsNotified) . ' clients ont été notifiés.');

            // Afficher la liste des clients notifiés
            foreach ($clientsNotified as $clientData) {
                $client = $clientData['client'];
                $this->line("Client ID: {$client->id}, Nom: {$client->surnom}, Téléphone: {$client->telephone}, Montant Restant: {$clientData['montantRestant']}, Date Échéance: {$clientData['date_echeance']->format('d/m/Y')}");
            }
            
        }

        return 0;
    }
}


