<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class Telephone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Vérifie que le numéro commence par 77, 78, 76, 70 ou 75 et qu'il a 9 chiffres
        if (!preg_match('/^(77|78|76|70|75)\d{7}$/', $value)) {
            $fail('Le numéro de téléphone doit commencer par 77, 78, 76, 70 ou 75 et être composé de 9 chiffres.');
        }
    }
}
