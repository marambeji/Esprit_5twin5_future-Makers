<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutritional_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->date('analysed_at');
            $table->decimal('energy_kcal', 6, 2);
            foreach (['protein', 'carbohydrates', 'sugars', 'fat', 'salt'] as $field) {
                $table->decimal($field, 5, 2);
            }
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutritional_analyses');
    }
};
