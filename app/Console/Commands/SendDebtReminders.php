<?php

namespace App\Console\Commands;

use App\Services\SmsDette;
use Illuminate\Console\Command;

class SendDebtReminders extends Command
{
    protected $signature = 'sms:send-debt-reminders';
    protected $description = 'Envoie des rappels de dettes par SMS à tous les clients ayant des dettes impayées';
    protected $smsDette;

    public function __construct(SmsDette $smsDette)
    {
        parent::__construct();
        $this->smsDette = $smsDette;
    }

    public function handle()
    {
        // Envoyer les rappels de dettes et obtenir les informations des clients traités
        $sentReminders = $this->smsDette->sendDebtReminders();

        // Afficher les informations des clients traités
        if (!empty($sentReminders)) {
            $this->info('Les rappels de dettes ont été envoyés aux clients suivants:');
            foreach ($sentReminders as $reminder) {
                $this->info("Client: {$reminder['client']}, Téléphone: {$reminder['telephone']}, Montant Restant: {$reminder['montant_restant']} FCFA");
            }
        } else {
            $this->info('Aucun rappel de dette n\'a été envoyé.');
        }
    }
}
