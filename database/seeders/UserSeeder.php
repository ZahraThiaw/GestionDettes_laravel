<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'un Admin
        User::create([
            'nom' => 'Admin',
            'prenom' => 'Admin',
            'login' => 'admin',
            'password' => Hash::make('admin12345'), // mot de passe hashé
            'role' => Role::Admin->value, // Utilisation de l'énumération Role
        ]);

        // Création d'un Boutiquier
        User::create([
            'nom' => 'Boutiquier',
            'prenom' => 'Boutiquier',
            'login' => 'boutiquier',
            'password' => Hash::make('boutiquier12345'), // mot de passe hashé
            'role' => Role::Boutiquier->value, // Utilisation de l'énumération Role
        ]);
        

        // Génère 10 utilisateurs fictifs
        //User::factory()->count(2)->create();
    }
}
