<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArticlesDisponiblesNotification extends Notification
{
    use Queueable;

    public $articlesDisponibles;

    public function __construct($articlesDisponibles)
    {
        $this->articlesDisponibles = $articlesDisponibles;
    }

    /**
     * Les canaux via lesquels la notification sera envoyée.
     */
    public function via($notifiable)
    {
        // On utilise les canaux mail et database
        return ['mail', 'database'];
    }

    /**
     * Le contenu de l'email envoyé au client.
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('Articles disponibles pour votre demande')
            ->line('Les articles suivants sont disponibles pour votre demande :');

        foreach ($this->articlesDisponibles as $article) {
            $mailMessage->line("Article: {$article['libelle']}, Quantité demandee: {$article['quantite_demande']}");
        }

        return $mailMessage->line('Merci pour votre patience.');
    }

    /**
     * Le contenu à enregistrer dans la base de données.
     */
    public function toDatabase($notifiable)
    {
        // Retourner les informations à stocker en base de données
        return [
            'message' => 'Voici les articles disponibles pour votre demande.',
            'articles_disponibles' => $this->articlesDisponibles,
        ];
    }
}
