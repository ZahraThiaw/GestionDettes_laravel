<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'surnom' => $this->faker->name,
            'telephone' => $this->faker->unique()->regexify('^(77|78|76|70|75)\d{6}$'),
            'adresse' => $this->faker->optional()->address,
            'user_id' => null, // Par défaut, l'utilisateur est nul
        ];
    }

    public function withUser()
    {
        return $this->afterCreating(function (Client $client) {
            $user = User::factory()->create([
                'role' => 'Client', // Spécifiez le rôle Client
            ]);
            $client->update(['user_id' => $user->id]);
        });
    }
}
