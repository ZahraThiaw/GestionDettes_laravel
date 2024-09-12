<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
{
    // Autorise l'utilisateur à faire cette requête
    public function authorize()
    {
        return true;  // Vous pouvez ajouter ici une logique d'autorisation si nécessaire
    }

    // Règles de validation
    public function rules()
    {
        return [
            'articles' => 'array|min:1',  // Vérifie que 'articles' est un tableau avec au moins un élément
            'articles.*.id' => 'required|integer', // Chaque article doit avoir un 'id' valide
            'articles.*.qteStock' => 'required|integer|min:1' // Chaque article doit avoir une 'qteStock' valide
        ];
    }

    // Messages d'erreur personnalisés (facultatif)
    public function messages()
    {
        return [
            'articles.array' => 'Les articles doivent être sous forme de tableau.',
            'articles.min' => 'Au moins un article doit être fourni.',
            'articles.*.id.required' => "L'ID de chaque article est obligatoire.",
            'articles.*.id.integer' => "L'ID de chaque article doit être un nombre entier.",
            'articles.*.qteStock.required' => "La quantité en stock est obligatoire pour chaque article.",
            'articles.*.qteStock.integer' => "La quantité en stock doit être un nombre entier.",
            'articles.*.qteStock.min' => "La quantité en stock doit être supérieur ou égale à 1."
        ];
    }
}

