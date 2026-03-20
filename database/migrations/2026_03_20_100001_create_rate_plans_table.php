<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 50);
            $table->string('code', 10);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_type_id', 'code']);
            $table->index(['room_type_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};
