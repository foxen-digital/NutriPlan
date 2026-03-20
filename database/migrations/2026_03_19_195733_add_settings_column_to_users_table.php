<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $column_name = config('model-settings.column', 'settings');
        if (!Schema::hasColumn('users', $column_name)) {
            Schema::table('users', function (Blueprint $table) use ($column_name) {
                $table->json($column_name)->nullable();
            });
        }
    }

    public function down(): void
    {
        $column_name = config('model-settings.column', 'settings');
        if (Schema::hasColumn('users', $column_name)) {
            Schema::table('users', function (Blueprint $table) use ($column_name) {
                $table->dropColumn($column_name);
            });
        }
    }
};
