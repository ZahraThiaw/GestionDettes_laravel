<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        // Génère une adresse email unique
        $email = $this->faker->unique()->safeEmail;

        // Génère un chemin temporaire pour l'image
        $imagePath = $this->faker->image('public/storage/images', 640, 480, 'people', false);

        // Lit le fichier image et le convertit en base64
        $imageData = Storage::get('public/' . $imagePath);
        $base64Image = base64_encode($imageData);

        return [
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'login' => $email, // Utilise un email pour le login
            'password' => Hash::make('password'), // Mot de passe par défaut
            'role_id' => Role::inRandomOrder()->first()->id, // Sélectionne un rôle aléatoire
            'photo' => 'data:image/jpeg;base64,' . $base64Image, // Photo en base64
        ];
    }
}
