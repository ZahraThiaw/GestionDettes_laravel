<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthentificationSanctum implements AuthentificationServiceInterface
{
    public function authentificate(array $credentials)
    {
        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Avec Sanctum, on utilise `plainTextToken`
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            return $token;
        }

        return null;
    }

    public function logout()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Révoquer tous les tokens du user
        $user->tokens()->delete();
    }
}
