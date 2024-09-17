<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelanceDemandeNotification extends Notification
{
    use Queueable;

    public $demande;

    public function __construct(Demande $demande)
    {
        $this->demande = $demande;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Notification par mail et stockage en base de données
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Nouvelle relance de demande')
                    ->greeting('Bonjour ' . $notifiable->name . ',')
                    ->line('Une nouvelle relance de demande a été créée.')
                    ->line('Demande ID: ' . $this->demande->id)
                    ->action('Voir la demande', url('/demandes/' . $this->demande->id))
                    ->line('Merci de vérifier les détails.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'demande_id' => $this->demande->id,
            'message' => 'Une nouvelle relance de demande a été créée.',
            'client_id' => $this->demande->client_id,
            'articles' => $this->demande->articles->map(function($article) {
                return [
                    'nom' => $article->nom,
                    'quantité' => $article->pivot->qte,
                ];
            }),
        ];
    }
}
