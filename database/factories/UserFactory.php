<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'login' => $this->faker->unique()->userName,
            'password' => Hash::make('password'), // Mot de passe par défaut
            'role_id' => Role::inRandomOrder()->first()->id, // Sélectionne un rôle aléatoire
            'photo' => $this->faker->imageUrl(640, 480, 'people'),
        ];
    }
}
