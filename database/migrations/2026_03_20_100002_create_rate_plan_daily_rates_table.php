<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_plan_daily_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('occupancy');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['rate_plan_id', 'date', 'occupancy']);
            $table->index(['rate_plan_id', 'date', 'occupancy', 'price'], 'rate_plan_daily_rates_covering_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plan_daily_rates');
    }
};
