<?php

namespace App\Http\Requests;

use App\Rules\CustomPassword;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Telephone;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modifier selon les besoins d'autorisation
    }

    public function rules()
    {
        return [
            'surnom' => 'required|string|max:255',
            'telephone' =>'required|string|unique:clients', new Telephone(),
            'adresse' => 'nullable|string|max:255',
            'user' => 'nullable|array',
            'user.nom' => 'required_with:user|string|max:255',
            'user.prenom' => 'required_with:user|string|max:255',
            'user.login' => 'required_with:user|string|max:255|unique:users,login',
            'user.password' => ['required_with:user', 'string', 'confirmed', new CustomPassword()], // Instanciation de la règle
            //'user.role' => ['required_with:user', 'string', 'in:Client'], // Assurez-vous que le rôle est Client
            'user.photo' => 'required_with:user|image|mimes:jpeg,png,jpg|max:40',
        ];
    }

    public function messages()
    {
        return [
            'surnom.required' => 'Le surnom est requis.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'telephone.unique' => 'Le numéro de téléphone existe déjà.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'user.nom.required_with' => 'Le nom est requis si des données utilisateur sont fournies.',
            'user.prenom.required_with' => 'Le prénom est requis si des données utilisateur sont fournies.',
            'user.login.required_with' => 'Le login est requis si des données utilisateur sont fournies.',
            'user.login.unique' => 'Le login doit être unique.',
            'user.password.required_with' => 'Le mot de passe est requis si des données utilisateur sont fournies.',
            'user.password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'user.password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'user.password.custom_password' => 'Le mot de passe ne répond pas aux critères de sécurité.',
            // 'user.role.required_with' => 'Le rôle est requis si des données utilisateur sont fournies.',
            // 'user.role.in' => 'Le rôle doit être Client.',
            'user.photo.required_with' => 'La photo est obligatoire.',
            'user.photo.image' => 'La photo doit être une image.',
            'user.photo.mimes' => 'La photo doit être de type jpeg, png, ou jpg.',
            'user.photo.max' => 'La taille de la photo ne doit pas dépasser 40 ko.',
            // Autres messages selon vos besoins
        ];
    }
}
