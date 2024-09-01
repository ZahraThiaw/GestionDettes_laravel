<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Modifier selon vos besoins d'autorisation
    }

    public function rules()
    {
        if ($this->isMethod('put')) {
            // Règles pour PUT (mise à jour complète)
            return [
                'libelle' => 'required|string|max:255|unique:articles,libelle,' . $this->route('id'),
                'prix' => 'required|numeric|min:0',
                'qteStock' => 'required|integer|min:0',
            ];
        } else {
            // Règles pour PATCH (mise à jour partielle)
            return [
                'libelle' => 'sometimes|required|string|max:255|unique:articles,libelle,' . $this->route('id'),
                'prix' => 'sometimes|required|numeric|min:0',
                'qteStock' => 'sometimes|required|integer|min:1',
            ];
        }
    }

    public function messages()
    {
        return [
            'libelle.required' => 'Le libellé de l\'article est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.unique' => 'Un article avec ce libellé existe déjà.',

            'prix.required' => 'Le prix de l\'article est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',

            'qteStock.required' => 'La quantité en stock est obligatoire.',
            'qteStock.integer' => 'La quantité en stock doit être un nombre entier.',
            'qteStock.min' => 'La quantité en stock doit etre supérieur ou égale à 1.',
        ];
    }
}
