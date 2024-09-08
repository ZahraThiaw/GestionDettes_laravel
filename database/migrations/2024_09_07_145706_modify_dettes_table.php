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
        // Modifier la table dettes
        Schema::table('dettes', function (Blueprint $table) {
            $table->dropColumn(['montantDu', 'montantRestant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les colonnes supprimées
        Schema::table('dettes', function (Blueprint $table) {
            $table->decimal('montantDu', 15, 2);
            $table->decimal('montantRestant', 15, 2);
        });
    }
};
