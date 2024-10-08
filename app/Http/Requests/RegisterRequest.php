<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CustomPassword;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Vous pouvez autoriser ou refuser la demande ici
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login',
            'password' => ['required', 'confirmed', new CustomPassword], // Utilisation de la règle CustomPassword
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:40',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'login.required' => 'Le login est obligatoire.',
            'login.string' => 'Le login doit être une chaîne de caractères.',
            'login.max' => 'Le login ne doit pas dépasser 255 caractères.',
            'login.unique' => 'Le login est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'photo.required' => 'La photo est obligatoire.',
            'photo.image' => 'La photo doit être une image.',
            'photo.max' => 'La taille de la photo ne doit pas dépasser 40 ko.',
        ];
    }
}
