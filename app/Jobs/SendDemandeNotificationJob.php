<?php

namespace App\Jobs;

use App\Models\Demande;
use App\Models\User;
use App\Notifications\DemandeCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDemandeNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $demande;

    public function __construct(Demande $demande)
    {
        $this->demande = $demande;
    }

    public function handle()
    {
        // Récupérer tous les utilisateurs ayant le rôle de Boutiquier
        $boutiquiers = User::whereHas('roles', function($query) {
            $query->where('name', 'Boutiquier');
        })->get();

        // Envoyer la notification à chaque Boutiquier
        foreach ($boutiquiers as $boutiquier) {
            $boutiquier->notify(new DemandeCreatedNotification());
        }
    }
}

