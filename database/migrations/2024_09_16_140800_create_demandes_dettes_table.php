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
        Schema::create('demandes_dettes', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Assurez-vous que la colonne est définie ici
            $table->unsignedBigInteger('client_id');
            $table->enum('status', ['En cours', 'Validée', 'Annulée'])->default('En cours');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('demandes_dettes');
    }
};
