<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetteRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clientId' => 'required|exists:clients,id',
            'articles' => 'required|array|min:1',
            'articles.*.articleId' => 'required|exists:articles,id',
            'articles.*.qteVente' => 'required|numeric|min:1',
            'articles.*.prixVente' => 'required|numeric|min:0',
            'paiement.montant' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'clientId.required' => 'Le client est obligatoire.',
            'clientId.exists'=> 'Le client n\'existe pas.',
            'articles.required' => 'Au moins un article doit être ajouté.',
            'articles.*.articleId.required' => 'L\'ID de l\'article est obligatoire.',
            'articles.*.qteVente.required' => 'La quantité de vente est obligatoire.',
            'paiement.montant.numeric' => 'Le montant du paiement doit être numérique.',
        ];
    }
}
