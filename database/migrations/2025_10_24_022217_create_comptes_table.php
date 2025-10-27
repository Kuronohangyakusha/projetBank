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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('numeroCompte')->unique();
            $table->string('type');
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise')->default('FCFA');
            $table->enum('statut', ['actif', 'bloque'])->default('actif');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
