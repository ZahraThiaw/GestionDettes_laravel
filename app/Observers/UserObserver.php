<?php

namespace App\Observers;

use App\Events\UserCreated;
use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        // Vérifier si l'utilisateur a téléchargé une photo
        if (request()->hasFile('photo')) {
            $file = request()->file('photo');
            
            // Sauvegarder le fichier temporairement dans le système de fichiers
            $tempPath = $file->store('temp');
            
            // Émettre l'événement UserCreated avec l'utilisateur et le chemin de la photo
            event(new UserCreated($user, $tempPath));
        }
    }
}
