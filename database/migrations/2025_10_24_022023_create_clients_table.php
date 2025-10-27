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
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('numeroCompte')->unique();
            $table->string('titulaire');
            $table->string('type'); // épargne, cheque
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise')->default('FCFA');
            $table->enum('statut', ['actif', 'bloque'])->default('actif');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes pour optimiser les performances
            $table->index('user_id');
            $table->index('numeroCompte');
            $table->index('statut');
            $table->index(['type', 'statut']);
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
