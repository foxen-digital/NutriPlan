<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\ShoppingListUpdated;
use App\Models\MealAssignment;
use App\Models\ShoppingList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateShoppingListJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [5, 15, 30];

    /**
     * Create a new job instance.
     *
     * @param  ShoppingList  $shoppingList  The shopping list to update
     * @param  MealAssignment  $meal  The meal assignment that triggered the update
     */
    public function __construct(
        public readonly ShoppingList $shoppingList,
        public readonly MealAssignment $meal
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Ensure relationships are loaded (defensive programming)
        $this->meal->loadMissing('mealPlanRecipe.recipe.ingredients');

        // Get the recipe through the relationship chain
        $mealPlanRecipe = $this->meal->mealPlanRecipe;
        $recipe = $mealPlanRecipe?->recipe;

        // Handle null safety - if recipe doesn't exist, skip silently
        if ($recipe === null) {
            Log::info('Shopping list update skipped - no recipe found', [
                'shopping_list_id' => $this->shoppingList->id,
                'meal_assignment_id' => $this->meal->id,
            ]);

            return;
        }

        // Get ingredients with pivot data
        $ingredients = $recipe->ingredients;

        // Compute starting order once to avoid N+1 queries in the loop
        $maxOrder = $this->shoppingList->items()->max('order') ?? 0;

        // Process each ingredient
        foreach ($ingredients as $ingredient) {
            $pivot = $ingredient->pivot;

            // Skip if amount is null or zero
            if ($pivot->amount === null || $pivot->amount <= 0) {
                continue;
            }

            // Check if item exists with matching ingredient_id and unit
            $existingItem = $this->shoppingList->items()
                ->where('ingredient_id', $ingredient->id)
                ->where('unit', $pivot->unit)
                ->first();

            if ($existingItem) {
                // Increment quantity and touch timestamp
                $existingItem->increment('quantity', $pivot->amount);
                $existingItem->touch();
            } else {
                $this->shoppingList->items()->create([
                    'name' => $ingredient->name,
                    'quantity' => $pivot->amount,
                    'unit' => $pivot->unit,
                    'category' => null,
                    'ingredient_id' => $ingredient->id,
                    'is_custom' => false,
                    'order' => ++$maxOrder,
                ]);
            }
        }

        // Dispatch event after successful processing
        $message = "List updated with new ingredients from {$recipe->title}";

        ShoppingListUpdated::dispatch(
            $this->shoppingList->user_id,
            $message,
            $this->shoppingList->id
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Shopping list update failed', [
            'shopping_list_id' => $this->shoppingList->id,
            'meal_assignment_id' => $this->meal->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
