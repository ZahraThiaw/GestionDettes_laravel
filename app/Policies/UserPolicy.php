<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function isAdmin(User $user)
    {
        return $user->role->name === 'Admin';
    }

    public function isBoutiquier(User $user)
    {
        return $user->role->name === 'Boutiquier';
    }

    public function isClient(User $user)
    {
        return $user->role->name === 'Client';
    }

    // Ajoutez d'autres méthodes en fonction des actions que vous souhaitez contrôler
}

