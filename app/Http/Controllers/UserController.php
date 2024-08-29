<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Boutiquier,Admin',
        ]);

        // Enregistrement
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);
        
        // Préparation de la réponse
        $response = [
            'statut' => 'success',
            'data' => $user,
            'message' => 'Utilisateur mis à jour avec succès.'
        ];

        return response()->json($response, 200); // Réponse avec code 200 (OK)
    }

    public function update(Request $request, $id)
    {
        // Trouver l'utilisateur
        $user = User::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'login' => 'string|max:255|unique:users,login,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'in:Boutiquier,Admin',
        ]);

        // Mise à jour
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);

        return response()->json($user, 200); // Réponse avec code 200 (OK)
    }


}
