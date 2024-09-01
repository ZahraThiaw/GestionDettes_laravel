<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'surnom' => $this->faker->unique()->word, // Assure l'unicité des surnoms
            'telephone' => $this->uniquePhoneNumber(), // Utilise une méthode pour garantir les contraintes du téléphone
            'adresse' => $this->faker->address,
            'user_id' => $this->assignUserId(), // Associe un utilisateur unique avec le rôle Client
        ];
    }

    /**
     * Génére un numéro de téléphone valide, unique et conforme aux critères.
     */
    private function uniquePhoneNumber(): string
    {
        // Génère un numéro de téléphone valide
        return $this->faker->unique()->numerify($this->faker->randomElement(['77########', '78########', '75########', '70########']));
    }

    /**
     * Associe un utilisateur avec le rôle Client, en s'assurant que l'ID est unique.
     */
    private function assignUserId(): ?int
    {
        // Récupère un utilisateur avec le rôle Client
        $user = User::whereHas('role', function ($query) {
            $query->where('name', 'Client');
        })->inRandomOrder()->first();

        // Retourne l'ID de l'utilisateur ou null si aucun utilisateur n'est trouvé
        return $user ? $user->id : null;
    }

    public function withoutAccount(): Factory
    {
        return $this->state([
            'user_id' => null,
        ]);
    }
}
