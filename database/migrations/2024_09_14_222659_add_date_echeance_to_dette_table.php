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
        Schema::table('dettes', function (Blueprint $table) {
            // Ajout de la colonne 'date_echeance' de type date
            $table->date('date_echeance')->nullable()->after('montant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettes', function (Blueprint $table) {
            // Suppression de la colonne 'date_echeance'
            $table->dropColumn('date_echeance');
        });
    }
};
