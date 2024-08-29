<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientUserSeeder extends Seeder
{
    public function run()
    {
        // Étape 1: Créer un client sans compte
        $client = Client::create([
            'surnom' => 'Client1',
            'telephone' => '77408647',
            'adresse' => '123 Rue Exemple',
        ]);

        // Étape 2: Créer un utilisateur avec le rôle 'Client'
        $user = User::create([
            'nom' => 'Mosciski',
            'prenom' => 'Reanna',
            'login' => 'koss.joana',
            'password' => Hash::make('password123'),
            'role' => 'Client', // Assurez-vous que cette valeur est acceptée
        ]);

        // Associer l'utilisateur au client existant
        $client->user_id = $user->id;
        $client->save();
    }
}

