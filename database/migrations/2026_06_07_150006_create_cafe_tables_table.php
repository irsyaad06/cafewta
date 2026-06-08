<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cafe_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_number')->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('status')->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_tables');
    }
};
