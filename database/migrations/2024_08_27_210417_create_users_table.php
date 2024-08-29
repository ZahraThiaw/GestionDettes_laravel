<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // clé primaire
            $table->string('nom'); // nom de l'utilisateur
            $table->string('prenom'); // prénom de l'utilisateur
            $table->string('login')->unique(); // login unique
            $table->string('password'); // mot de passe hashé
            $table->enum('role', array_column(Role::cases(), 'value')); // Utilisation de l'énumération Role
            $table->timestamps(); // colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
