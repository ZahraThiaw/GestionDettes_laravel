<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthentificationPassport implements AuthentificationServiceInterface
{
    public function authentificate(array $credentials)
    {
        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->accessToken;
            return $token;
        }

        return null;
    }

    public function logout()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Révoquer le jeton d'accès actuel
        $user->token()->revoke();
    }
}
