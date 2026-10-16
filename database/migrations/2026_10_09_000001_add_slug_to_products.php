<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->string('slug')->nullable()->unique());
        foreach (DB::table('products')->orderBy('id')->get(['id', 'name']) as $product) {
            $base = Str::slug($product->name) ?: 'produit';
            if (ctype_digit($base)) {
                $base = 'produit-'.$base;
            }
            $slug = $base;
            $suffix = 2;
            while (DB::table('products')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
