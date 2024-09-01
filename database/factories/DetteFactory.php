<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetteFactory extends Factory
{
    protected $model = \App\Models\Dette::class;

    public function definition()
    {
        $montant = $this->faker->randomFloat(2, 50, 1000); // Montant total entre 50 et 1000
        $montantDu = $this->faker->randomFloat(2, 0, $montant); // Montant payé
        $montantRestant = $montant - $montantDu; // Calcul du montant restant

        return [
            'date' => $this->faker->date(), // Date aléatoire
            'montant' => $montant,
            'montantDu' => $montantDu,
            'montantRestant' => $montantRestant,
            'client_id' => Client::factory(), // Associer un client
        ];
    }
}

