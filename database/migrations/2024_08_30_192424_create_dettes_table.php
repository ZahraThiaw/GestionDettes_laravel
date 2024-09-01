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
        Schema::create('dettes', function (Blueprint $table) {
            $table->id(); // Clé primaire
            $table->date('date'); // Date de la dette
            $table->decimal('montant', 15, 2); // Montant total de la dette
            $table->decimal('montantDu', 15, 2); // Montant payé
            $table->decimal('montantRestant', 15, 2); // Montant restant à payer
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Relation avec client
            $table->timestamps(); // Attributs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dettes');
    }
};
