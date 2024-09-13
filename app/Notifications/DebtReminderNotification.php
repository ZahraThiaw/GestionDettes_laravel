<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DebtReminderNotification extends Notification
{
    protected $montantRestant;
    protected $clientName;
    protected $clientPhoneNumber;

    public function __construct($clientName, $clientPhoneNumber, $montantRestant)
    {
        $this->montantRestant = $montantRestant;
        $this->clientName = $clientName;
        $this->clientPhoneNumber = $clientPhoneNumber;
    }

    public function via($notifiable)
    {
        return ['database', 'sms'];
    }

    // Déclaration correcte de la méthode toSms
    public function toSms($notifiable)
    {
        return [
            'to' => $this->clientPhoneNumber,
            'client_name' => $this->clientName,
            'amount' => $this->montantRestant,
        ];
    }

    // Méthode pour le stockage dans la base de données
    public function toDatabase($notifiable)
    {
        return [
            'message' => "Cher(e) {$this->clientName}, vous avez un montant restant de {$this->montantRestant} FCFA à régler. Merci de procéder au paiement."
        ];
    }
}
