<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Jobs\RetryImageUpload;
use Illuminate\Support\Facades\Log;

class RetryFailedUploads extends Command
{
    // Nom de la commande
    protected $signature = 'uploads:retry';

    // Description de la commande
    protected $description = 'Relance le job pour envoyer les photos locales vers le cloud';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Récupérer tous les utilisateurs avec une photo enregistrée localement
        $users = User::where('photo', 'LIKE', '%/storage/photos/%')->get();

        if ($users->isEmpty()) {
            $this->info("Aucune photo locale à renvoyer.");
            return;
        }

        foreach ($users as $user) {
            // Extraire le chemin local de la photo
            $localPath = str_replace('/storage', 'public', parse_url($user->photo, PHP_URL_PATH));

            if (Storage::exists($localPath)) {
                // Lancer le job pour retenter l'envoi au cloud
                RetryImageUpload::dispatch($user, $localPath);
                $this->info("Relance de l'envoi pour l'utilisateur ID: {$user->id}");
            } else {
                $this->warn("Le fichier pour l'utilisateur ID: {$user->id} n'existe plus localement.");
            }
        }

        Log::info("Commande terminée : RetryFailedUploads");
        $this->info("Processus terminé.");
    }
}
