<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DemandeValidationNotification extends Notification
{
    use Queueable;

    protected $demande;
    protected $motif;

    public function __construct($demande, $motif)
    {
        $this->demande = $demande;
        $this->motif = $motif;
    }

    public function via($notifiable)
    {
        return ['database']; // Utilisez les canaux appropriés
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Votre demande a été validée')
                    ->line('Votre demande a été validée.')
                    ->line('Motif : ' . $this->motif)
                    ->line('Veuillez passer à la boutique pour récupérer vos produits.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Votre demande a été validée.',
            'motif' => $this->motif,
            'demande_id' => $this->demande->id,
        ];
    }
}
