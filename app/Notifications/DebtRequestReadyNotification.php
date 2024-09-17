<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DebtRequestReadyNotification extends Notification
{
    use Queueable;

    protected $demande;

    public function __construct(Demande $demande)
    {
        $this->demande = $demande;
    }

    /**
     * Détermine les canaux par lesquels la notification sera envoyée.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Ajoutez 'mail' ou 'sms' si nécessaire.
    }

    /**
     * Prépare les données pour la notification dans la base de données.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'client' => $this->demande->client->surnom,
            'message' => "Votre demande de dette a été validée. Veuillez passer prendre les produits au niveau de la boutique.",
        ];
    }

    /**
     * Prépare les données pour la notification par SMS.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toSms($notifiable)
    {
        return [
            'to' => $notifiable->phone_number,
            'message' => "Votre demande de dette est prête. Passez prendre les produits à la boutique.",
        ];
    }
}
