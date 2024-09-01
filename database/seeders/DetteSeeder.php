<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dette;
use App\Models\Client;

class DetteSeeder extends Seeder
{
    public function run()
    {
        // Générer 5 clients avec dettes
        Client::all()->each(function ($client) {
            // Générer entre 1 et 5 dettes pour chaque client
            Dette::factory()->count(rand(1, 5))->create([
                'client_id' => $client->id,
            ]);
        });
    }
}
