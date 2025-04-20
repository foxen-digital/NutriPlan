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
        Schema::table('meal_assignments', function (Blueprint $table): void {
            $table->unsignedInteger('order')->default(0)->after('to_cook');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_assignments', function (Blueprint $table): void {
            $table->dropColumn('order');
        });
    }
};
