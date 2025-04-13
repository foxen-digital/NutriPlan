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
        Schema::create('ingredient_recipe', function (Blueprint $table): void {
            $table->id(); // Auto-incrementing primary key
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('unit')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Create an index for faster lookups, but allow duplicates
            $table->index(['ingredient_id', 'recipe_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_recipe');
    }
};
