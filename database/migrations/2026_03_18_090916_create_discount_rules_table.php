<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('type', ['long_stay', 'last_minute']);
            $table->unsignedSmallInteger('min_nights')->nullable();
            $table->unsignedSmallInteger('within_days')->nullable();
            $table->decimal('discount_percentage', 5, 2);
            $table->unsignedTinyInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Lookup index: filter by type and active status for discount resolution
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};
