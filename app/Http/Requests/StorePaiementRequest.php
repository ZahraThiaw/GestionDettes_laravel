<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaiementRequest extends FormRequest
{
    public function authorize()
    {
        // Autoriser toutes les requêtes pour cette démonstration
        return true;
    }

    public function rules()
    {
        return [
            'montant' => 'required|numeric|min:1', // Le montant est requis, numérique et positif
        ];
    }

    public function messages()
    {
        return [
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à zéro.',
        ];
    }
}
