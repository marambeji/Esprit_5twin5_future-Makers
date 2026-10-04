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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('label_id')
                ->constrained('labels')
                ->onDelete('cascade');

            $table->string('nom');
            $table->string('numero_certificat')->unique();
            $table->date('date_obtention');
            $table->date('date_expiration')->nullable();

            $table->enum('statut', [
                'valide',
                'expiree',
                'suspendue'
            ])->default('valide');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
