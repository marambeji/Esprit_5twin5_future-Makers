<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fermes', function (Blueprint $table) {
            $table->text('boundary_geojson')->nullable();
            $table->double('boundary_area_m2')->nullable();
            $table->double('boundary_perimeter_m')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fermes', function (Blueprint $table) {
            $table->dropColumn(['boundary_geojson', 'boundary_area_m2', 'boundary_perimeter_m']);
        });
    }
};
