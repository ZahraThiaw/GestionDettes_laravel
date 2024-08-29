<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CustumPassword;
use App\Enums\Role; // Assurez-vous d'importer l'énumération Role

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modifier selon les besoins d'autorisation
    }

    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login',
            'password' => ['required', 'string', 'confirmed', CustumPassword::class], // Confirmation du mot de passe
            'role' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Role::tryFrom($value)) {
                        $fail('Le rôle doit être valide selon les énumérations définies.');
                    }
                },
            ], // Validation avec Enum
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'login.required' => 'Le login est requis.',
            'login.unique' => 'Le login doit être unique.',
            'password.required' => 'Le mot de passe est requis.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.custom_password' => 'Le mot de passe ne répond pas aux critères de sécurité.',
            'role.required' => 'Le rôle est requis.',
            'role.in' => 'Le rôle doit être valide selon les énumérations définies.',
        ];
    }
}
