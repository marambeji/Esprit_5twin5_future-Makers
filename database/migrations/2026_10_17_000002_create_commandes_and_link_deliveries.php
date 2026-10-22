<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantite');
            $table->string('adresse_livraison', 255);
            $table->string('telephone', 30);
            $table->string('statut', 20)->default('en_attente');
            $table->text('motif_refus')->nullable();
            $table->timestamps();
        });
        // Nullable : les anciennes livraisons de démonstration restent valides.
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('commande_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', fn (Blueprint $table) => $table->dropConstrainedForeignId('commande_id'));
        Schema::dropIfExists('commandes');
    }
};
