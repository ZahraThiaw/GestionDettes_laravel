<?php

namespace App\Console\Commands;

use App\Services\SmsDette;
use Illuminate\Console\Command;

class SendDebtReminders extends Command
{
    protected $signature = 'sms:send-debt-reminders';
    protected $description = 'Envoie des rappels de dettes par SMS et affiche les clients concernés';

    protected $smsDette;

    public function __construct(SmsDette $smsDette)
    {
        parent::__construct();
        $this->smsDette = $smsDette;
    }

    public function handle()
    {
        $clients = $this->smsDette->sendDebtReminders();

        // Afficher les informations des clients
        $this->info("Les rappels de dettes ont été envoyés aux clients suivants :");
        foreach ($clients as $clientData) {
            $client = $clientData['client'];
            $montantRestant = $clientData['montantRestant'];
            $this->line("Client: {$client->surnom}, Téléphone: {$client->telephone}, Montant Restant: $montantRestant FCFA");
        }

        return 0;
    }
}
