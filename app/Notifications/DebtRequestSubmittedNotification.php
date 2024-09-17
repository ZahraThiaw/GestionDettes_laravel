<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DebtRequestSubmittedNotification extends Notification
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
        return ['database', 'sms'];
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
            'articles' => $this->demande->articles->map(function($article) {
                return [
                    'libelle' => $article->libelle,
                    'qteVente' => $article->pivot->qte,
                ];
            })->toArray(),
            'message' => "Nouvelle demande de dette soumise par {$this->demande->client->surnom}.",
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
            'message' => "Nouvelle demande de dette soumise par {$this->demande->client->surnom}.",
        ];
    }
}
