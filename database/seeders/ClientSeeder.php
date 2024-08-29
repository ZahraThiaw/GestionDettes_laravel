<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run()
    {
        Client::factory()->count(1)->create(); // Crée 10 clients sans compte utilisateur
    }
}
