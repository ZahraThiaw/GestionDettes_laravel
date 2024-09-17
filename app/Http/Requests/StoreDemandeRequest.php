<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Vous pouvez ajouter des conditions d'autorisation ici
    }

    public function rules()
    {
        return [
            'articles' => 'required|array',
            'articles.*.libelle' => 'required|exists:articles,libelle',
            'articles.*.qte' => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'articles.required' => 'Au moins un article doit être ajoute.',
            'articles.*.libelle.required' => 'L\'article est obligatoire.',
            'articles.*.libelle.exists' => 'L\'article n\'existe pas.',
            'articles.*.qte.required' => 'La quantité de l\'article est obligatoire.',
        ];
    }
}
