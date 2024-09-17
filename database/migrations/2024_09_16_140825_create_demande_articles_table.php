<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('demande_articles', function (Blueprint $table) {
            $table->id(); // ID auto-incrémenté
            $table->foreignId('demande_id')->constrained('demandes_dettes')->onDelete('cascade'); // Clé étrangère vers la table demandes_dettes
            $table->foreignId('article_id')->constrained()->onDelete('cascade'); // Clé étrangère vers la table articles
            $table->integer('qte'); // Quantité vendue
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demande_articles');
    }

};
