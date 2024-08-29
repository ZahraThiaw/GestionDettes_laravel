<?php

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
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // Clé primaire
            $table->string('surnom');
            $table->string('telephone')->unique();
            $table->string('adresse')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Clé étrangère vers users
            $table->timestamps(); // Attributs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
