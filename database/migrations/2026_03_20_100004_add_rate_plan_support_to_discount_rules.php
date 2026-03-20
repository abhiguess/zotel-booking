<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN type ENUM('long_stay','last_minute','early_bird') NOT NULL");

        Schema::table('discount_rules', function (Blueprint $table) {
            $table->foreignId('rate_plan_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('min_days_before')->nullable()->after('within_days');

            $table->index(['rate_plan_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('discount_rules', function (Blueprint $table) {
            $table->dropForeign(['rate_plan_id']);
            $table->dropIndex(['rate_plan_id', 'type', 'is_active']);
            $table->dropColumn(['rate_plan_id', 'min_days_before']);
        });

        DB::statement("ALTER TABLE discount_rules MODIFY COLUMN type ENUM('long_stay','last_minute') NOT NULL");
    }
};
