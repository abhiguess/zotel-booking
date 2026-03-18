<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price_1p', 10, 2);
            $table->decimal('price_2p', 10, 2);
            $table->decimal('price_3p', 10, 2);
            $table->decimal('breakfast_price_pp', 10, 2);
            $table->unsignedSmallInteger('total_rooms');
            $table->unsignedSmallInteger('booked_rooms')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            // Prevents duplicate entries per room type per date — primary lookup index
            $table->unique(['room_type_id', 'date']);

            // Date range scans for search queries
            $table->index('date');

            // Covering index for availability check: avoids table lookups when checking room availability
            $table->index(['room_type_id', 'date', 'booked_rooms', 'total_rooms'], 'idx_availability_check');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_inventory');
    }
};
