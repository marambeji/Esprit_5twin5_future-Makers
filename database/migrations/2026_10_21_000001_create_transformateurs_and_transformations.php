<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Module Transformation (Zied) : maillon « transformateur » du parcours ferme → assiette.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transformateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('type_activite', 50);
            $table->string('email')->unique();
            $table->string('telephone', 30);
            $table->string('ville', 100);
            $table->string('adresse');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('transformations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('transformateur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ferme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('procede', 50);
            $table->date('date_transformation');
            $table->decimal('quantite_kg', 10, 2);
            $table->decimal('co2_kg', 10, 2)->nullable();
            $table->decimal('eau_litres', 12, 2)->nullable();
            $table->decimal('energie_kwh', 10, 2)->nullable();
            $table->char('eco_score', 1)->nullable();
            $table->string('allegation')->nullable();
            $table->string('verdict_allegation', 20)->nullable();
            $table->text('analyse_ia')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transformations');
        Schema::dropIfExists('transformateurs');
    }
};
