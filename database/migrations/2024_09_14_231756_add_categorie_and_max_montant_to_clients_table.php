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
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('categorie_id')->default(3); // Catégorie par défaut (Bronze)
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
            $table->decimal('max_montant', 10, 2)->nullable(); // Rempli uniquement pour la catégorie Silver
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn(['categorie_id', 'max_montant']);
        });
    }

};
