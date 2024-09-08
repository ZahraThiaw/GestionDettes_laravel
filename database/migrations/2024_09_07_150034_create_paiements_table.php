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
        // Créer la table paiements
        Schema::create('paiements', function (Blueprint $table) {
            $table->id(); // Clé primaire
            $table->decimal('montant', 15, 2); // Montant payé
            $table->foreignId('dette_id')->constrained('dettes')->onDelete('cascade'); // Relation avec dette
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la table paiements
        Schema::dropIfExists('paiements');
    }
};
