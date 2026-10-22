<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('email', 150)->unique();
            $table->string('phone', 30);
            $table->string('city', 100);
            $table->string('address', 255);
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('distributor_id')->constrained()->restrictOnDelete();
            $table->string('destination', 255);
            $table->date('delivery_date');
            $table->string('status', 20)->default('planifiee');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('distributors');
    }
};
