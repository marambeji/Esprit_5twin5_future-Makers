<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutritional_analyses', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_fdc_id')->nullable();
            $table->string('reference_description', 1000)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nutritional_analyses', fn (Blueprint $table) => $table->dropColumn(['reference_fdc_id', 'reference_description']));
    }
};
