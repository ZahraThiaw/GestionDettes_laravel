<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;

class ClientSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer 10 clients avec un compte utilisateur
        Client::factory()
            ->count(5)
            ->create();

        // Créer 5 clients sans compte utilisateur
        Client::factory()
            ->count(5)
            ->withoutAccount()
            ->create();
    }
}
