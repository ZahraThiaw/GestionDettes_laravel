<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DemandeStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $status;
    protected $motif;

    public function __construct(string $status, ?string $motif = null)
    {
        $this->status = $status;
        $this->motif = $motif;
    }

    /**
     * Détermine les canaux par lesquels la notification sera envoyée.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Vous pouvez ajouter d'autres canaux comme 'mail', 'sms', etc.
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
            'status' => $this->status,
            'motif' => $this->motif,
            'message' => $this->status === 'Annulée'
                ? "Votre demande a été annulée. Motif : {$this->motif}."
                : "Votre demande a été validée.",
        ];
    }
}
