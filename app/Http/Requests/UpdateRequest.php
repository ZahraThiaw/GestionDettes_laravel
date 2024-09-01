<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Telephone;
use App\Rules\CustumPassword;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modifier selon les besoins d'autorisation
    }

    public function rules()
    {
        return [
            'surnom' => 'Sometimes|string|max:255',
            'telephone' => ['Sometimes', 'string', Telephone::class],
            'adresse' => 'Sometimes|string|max:255',
            'user.nom' => 'Sometimes|string|max:255',
            'user.prenom' => 'Sometimes|string|max:255',
            'user.login' => 'Sometimes|string|max:255|unique:users,login,' . $this->user->id,
            'user.password' => ['Sometimes', 'string', 'confirmed', CustumPassword::class],
            'user.role' => ['Sometimes', 'string', 'in:Client'],
        ];
    }

    public function messages()
    {
        return [
            'surnom.required' => 'Le surnom est requis.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'user.nom.string' => 'Le nom doit être une chaîne de caractères.',
            'user.prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'user.login.unique' => 'Le login doit être unique.',
            'user.password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'user.password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'user.password.custom_password' => 'Le mot de passe ne répond pas aux critères de sécurité.',
            'user.role.in' => 'Le rôle doit être un rôle valide (Client).',
            // Autres messages selon vos besoins
        ];
    }
}
