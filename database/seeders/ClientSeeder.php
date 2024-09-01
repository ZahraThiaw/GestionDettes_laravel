<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run()
    {
        // Créez des clients avec un utilisateur associé
        Client::factory()->count(5)->create();

        // Créez des clients sans utilisateur associé
        Client::factory()->count(5)->withoutAccount()->create();
    }
}
