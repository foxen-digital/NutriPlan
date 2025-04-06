<?php

declare(strict_types=1);

namespace Tests\Feature\ShoppingList;

use App\Enums\MeasurementUnit;
use App\Models\Ingredient;
use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingListGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_shopping_list_from_meal_plan(): void
    {
        // Create a user and a meal plan
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create([
            'user_id' => $user->id,
            'start_date' => now(),
            'duration' => 7,
            'people_count' => 2,
        ]);

        // Create a recipe with ingredients
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'servings' => 4,
        ]);

        // Attach ingredients to the recipe
        $flour = Ingredient::factory()->create(['name' => 'Flour']);
        $sugar = Ingredient::factory()->create(['name' => 'Sugar']);

        $recipe->ingredients()->attach($flour, ['amount' => 2, 'unit' => MeasurementUnit::CUP->value]);
        $recipe->ingredients()->attach($sugar, ['amount' => 1, 'unit' => MeasurementUnit::CUP->value]);

        // Add recipe to meal plan
        $mealPlanRecipe = MealPlanRecipe::create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id,
            'scale_factor' => 1.0,
            'servings_available' => 2.0,
        ]);

        // Create day in meal plan
        $day = MealPlanDay::create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1,
        ]);

        // Create meal assignment with to_cook = true
        MealAssignment::create([
            'meal_plan_day_id' => $day->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'servings' => 2.0,
            'to_cook' => true,
        ]);

        // Generate shopping list
        $response = $this->actingAs($user)
            ->post(route('meal-plans.generate-shopping-list', $mealPlan->id), [
                'name' => 'Test Shopping List',
                'period' => 'full',
            ]);

        $response->assertRedirect(route('shopping-lists.show', 1));
        $response->assertSessionHas('success', 'Shopping list generated successfully.');

        // Check that shopping list was created with correct data
        $this->assertDatabaseHas('shopping_lists', [
            'user_id' => $user->id,
            'name' => 'Test Shopping List',
        ]);

        // Check that shopping list items were created
        $this->assertDatabaseHas('shopping_list_items', [
            'shopping_list_id' => 1,
            'ingredient_id' => $flour->id,
            'name' => 'Flour',
            'quantity' => 2.0,
            'unit' => 'cup',
            'is_custom' => false,
        ]);

        $this->assertDatabaseHas('shopping_list_items', [
            'shopping_list_id' => 1,
            'ingredient_id' => $sugar->id,
            'name' => 'Sugar',
            'quantity' => 1.0,
            'unit' => 'cup',
            'is_custom' => false,
        ]);
    }

    public function test_only_owner_can_generate_shopping_list_from_meal_plan(): void
    {
        // Create two users
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create a meal plan owned by the first user
        $mealPlan = MealPlan::factory()->create([
            'user_id' => $owner->id,
            'start_date' => now(),
            'duration' => 7,
        ]);

        // Try to generate a shopping list as the other user
        $response = $this->actingAs($otherUser)
            ->post(route('meal-plans.generate-shopping-list', $mealPlan->id), [
                'period' => 'full',
            ]);

        // Should return 403 Forbidden
        $response->assertForbidden();
    }

    public function test_shopping_list_generation_validates_period(): void
    {
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create([
            'user_id' => $user->id,
            'start_date' => now(),
            'duration' => 7, // 7-day plan, not 14
        ]);

        // Try to generate with an invalid period for a 7-day plan
        $response = $this->actingAs($user)
            ->post(route('meal-plans.generate-shopping-list', $mealPlan->id), [
                'period' => 'week2', // Invalid for a 7-day plan
            ]);

        $response->assertSessionHasErrors('period');
    }
}
