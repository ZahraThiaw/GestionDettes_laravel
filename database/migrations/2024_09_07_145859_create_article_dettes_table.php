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
        // Créer la table article_dette
        Schema::create('article_dettes', function (Blueprint $table) {
            $table->id(); // Clé primaire
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade'); // Relation avec article
            $table->foreignId('dette_id')->constrained('dettes')->onDelete('cascade'); // Relation avec dette
            $table->integer('qteVente'); // Quantité vendue
            $table->decimal('prixVente', 15, 2); // Prix de vente de l'article
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la table article_dette
        Schema::dropIfExists('article_dettes');
    }
};
