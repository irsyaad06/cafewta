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
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
        
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique();
        });
        
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique();
        });
    }
};
