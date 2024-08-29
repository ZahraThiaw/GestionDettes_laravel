<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustumPassword implements ValidationRule
{
    /**
     * Applique la validation à l'attribut donné.
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Utiliser la validation par défaut de Laravel pour les mots de passe
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => Password::defaults()] // Appliquer les règles par défaut de Laravel
        );

        // Si la validation échoue, on déclenche une erreur
        if ($validator->fails()) {
            $fail('Le mot de passe ne répond pas aux critères de sécurité.');
        }
    }
}
