<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DebtReminderNotification extends Notification
{
    protected $montantRestant;
    protected $clientName;
    protected $clientPhoneNumber;
    protected $message;

    public function __construct($clientName, $clientPhoneNumber, $montantRestant, $message)
    {
        $this->montantRestant = $montantRestant;
        $this->clientName = $clientName;
        $this->clientPhoneNumber = $clientPhoneNumber;
        $this->message = $message;
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
            'message' => $this->message
        ];
    }

    // Méthode pour le stockage dans la base de données
    public function toDatabase($notifiable)
    {
        return [
            $this->message
            //'message' => "Cher(e) {$this->clientName}, vous avez un montant restant de {$this->montantRestant} FCFA à régler. Merci de procéder au paiement."
        ];
    }
}
