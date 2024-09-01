<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Définition des règles pour le mot de passe
        $rules = Password::min(5)
            ->letters()          // Doit contenir au moins une lettre
            ->mixedCase()        // Doit contenir des lettres majuscules et minuscules
            ->numbers()          // Doit contenir au moins un chiffre
            ->symbols();         // Doit contenir au moins un caractère spécial

        // Création du validateur
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => $rules],
            [
                // Messages d'erreur personnalisés
                'min' => 'Le :attribute doit contenir au moins :min caractères.',
                'letters' => 'Le :attribute doit contenir au moins une lettre.',
                'mixed_case' => 'Le :attribute doit contenir des lettres majuscules et minuscules.',
                'numbers' => 'Le :attribute doit contenir au moins un chiffre.',
                'symbols' => 'Le :attribute doit contenir au moins un caractère spécial.',
            ]
        );

        // Si la validation échoue, on déclenche une erreur
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
