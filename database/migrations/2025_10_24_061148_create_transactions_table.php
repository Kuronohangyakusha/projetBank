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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id');
            $table->string('type'); // depot, retrait, virement
            $table->decimal('montant', 15, 2);
            $table->string('devise')->default('FCFA');
            $table->text('description')->nullable();
            $table->uuid('compte_destination_id')->nullable(); // pour les virements
            $table->enum('statut', ['en_attente', 'validee', 'rejete'])->default('en_attente');
            $table->json('metadata')->nullable();
            $table->timestamp('date_execution')->nullable();
            $table->timestamps();

            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->foreign('compte_destination_id')->references('id')->on('comptes')->onDelete('set null');

            // Indexes pour optimiser les performances
            $table->index('compte_id');
            $table->index('compte_destination_id');
            $table->index('statut');
            $table->index('type');
            $table->index('date_execution');
            $table->index(['type', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
