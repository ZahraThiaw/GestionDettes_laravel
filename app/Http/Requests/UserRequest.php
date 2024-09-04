<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CustomPassword;
use App\Models\Role;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validRoles = Role::whereIn('name', ['Admin', 'Boutiquier'])->pluck('id')->toArray();

        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|email|max:255|unique:users,login',
            'password' => ['required', 'confirmed', new CustomPassword],
            'role_id' => ['required', 'integer', 'exists:roles,id', function ($attribute, $value, $fail) use ($validRoles) {
                if (!in_array($value, $validRoles)) {
                    $fail('Le rôle sélectionné est invalide. Choisissez Admin ou Boutiquier.');
                }
            }],
            'photo' => 'required|image|mimes:jpeg,png,jpg,svg|max:40',
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'login.required' => 'Le login est obligatoire.',
            'login.email' => 'Le login doit être une adresse email valide.',
            'login.max' => 'Le login ne doit pas dépasser 255 caractères.',
            'login.unique' => 'Le login est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'role_id.required' => 'Le rôle est obligatoire.',
            'role_id.exists' => 'Le rôle sélectionné est invalide.',
            'photo.required' => 'La photo est obligatoire.',
            'photo.image' => 'La photo doit être une image.',
            'photo.mimes' => 'La photo doit être de type jpeg, png, ou jpg.',
            'photo.max' => 'La taille de la photo ne doit pas dépasser 40ko.',
        ];
    }
}
