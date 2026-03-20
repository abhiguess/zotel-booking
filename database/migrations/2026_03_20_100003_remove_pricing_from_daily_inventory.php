<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_inventory', function (Blueprint $table) {
            $table->dropColumn(['price_1p', 'price_2p', 'price_3p', 'breakfast_price_pp']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_inventory', function (Blueprint $table) {
            $table->decimal('price_1p', 10, 2)->default(0)->after('date');
            $table->decimal('price_2p', 10, 2)->default(0)->after('price_1p');
            $table->decimal('price_3p', 10, 2)->default(0)->after('price_2p');
            $table->decimal('breakfast_price_pp', 10, 2)->default(0)->after('price_3p');
        });
    }
};
