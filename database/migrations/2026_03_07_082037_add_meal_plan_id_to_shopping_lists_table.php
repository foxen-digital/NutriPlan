<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->foreignId('meal_plan_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_lists', function (Blueprint $table) {
            $table->dropForeign(['meal_plan_id']);
            $table->dropColumn('meal_plan_id');
        });
    }
};
