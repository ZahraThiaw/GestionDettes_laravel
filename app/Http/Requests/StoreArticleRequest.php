<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modifier selon vos besoins d'autorisation
    }

    public function rules()
    {
        return [
            'libelle' => 'required|string|max:255|unique:articles,libelle',
            'prix' => 'required|numeric|min:1',
            'qteStock' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'libelle.required' => 'Le libellé est requis.',
            'libelle.unique' => 'Un article avec ce libellé existe déjà.',
            'prix.required' => 'Le prix est requis.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix doit être supérieur ou égal à 0.',
            'qteStock.required' => 'La quantité en stock est requise.',
            'qteStock.integer' => 'La quantité en stock doit être un nombre entier.',
            'qteStock.min' => 'La quantité en stock ne peut pas être négative.',
        ];
    }
}
