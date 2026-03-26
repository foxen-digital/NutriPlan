<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meal_plan_recipe', function (Blueprint $table): void {
            $table->decimal('servings_available', 8, 2)->nullable()->after('scale_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_plan_recipe', function (Blueprint $table): void {
            $table->dropColumn('servings_available');
        });
    }
};
