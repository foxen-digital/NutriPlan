<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('glucose_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('glucose_mmol_l', 5, 2);
            $table->enum('reading_type', ['fasting', 'pre_meal', 'post_meal', 'bedtime', 'other']);
            $table->timestamp('measured_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'measured_at']);
            $table->index('reading_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glucose_entries');
    }
};
