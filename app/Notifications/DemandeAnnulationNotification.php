<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DemandeAnnulationNotification extends Notification
{
    use Queueable;

    protected $motif;

    public function __construct($motif)
    {
        $this->motif = $motif;
    }

    public function via($notifiable)
    {
        return ['database']; // Utilisez les canaux appropriés
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Votre demande a été annulée')
                    ->line('Votre demande a été annulée.')
                    ->line('Motif : ' . $this->motif);
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Votre demande a été annulée.',
            'motif' => $this->motif,
        ];
    }
}
