<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealAssignmentMoveControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_move_assignment_to_another_day(): void
    {
        // Arrange
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1
        ]);

        $day2 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 2
        ]);

        $assignment = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day1->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Act
        $response = $this->actingAs($user)
            ->patch(route('meal-assignments.move', $assignment), [
                'new_meal_plan_day_id' => $day2->id
            ]);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('meal_assignments', [
            'id' => $assignment->id,
            'meal_plan_day_id' => $day2->id,
            'to_cook' => true // Still true as it's the only assignment
        ]);
    }

    public function test_to_cook_flag_is_preserved_when_moving_assignment_to_earlier_day(): void
    {
        // Arrange
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1
        ]);

        $day3 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 3
        ]);

        // Assignment on day 3
        $assignment = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day3->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Act: Move assignment from day 3 to day 1 (earlier)
        $response = $this->actingAs($user)
            ->patch(route('meal-assignments.move', $assignment), [
                'new_meal_plan_day_id' => $day1->id
            ]);

        // Assert: to_cook should still be true
        $response->assertOk();

        $this->assertDatabaseHas('meal_assignments', [
            'id' => $assignment->id,
            'meal_plan_day_id' => $day1->id
        ]);

        $assignment->refresh();
        $this->assertTrue($assignment->to_cook);
    }

    public function test_to_cook_flag_changes_when_recipe_has_multiple_assignments(): void
    {
        // Arrange
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1
        ]);

        $day2 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 2
        ]);

        $day3 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 3
        ]);

        // First assignment on day 1 - to_cook = true (the earliest, so should be to_cook=true)
        $assignment1 = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day1->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Second assignment for same recipe on day 3 - to_cook = false
        $assignment2 = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day3->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => false
        ]);

        // Act: Move first assignment to day 2 (this should keep assignment1 as to_cook=true)
        $response = $this->actingAs($user)
            ->patch(route('meal-assignments.move', $assignment1), [
                'new_meal_plan_day_id' => $day2->id
            ]);

        // Assert
        $response->assertOk();

        // Refresh both assignments
        $assignment1->refresh();
        $assignment2->refresh();

        // Assignment1 moved to day2 but is still the first chronologically, so to_cook should be true
        $this->assertEquals($day2->id, $assignment1->meal_plan_day_id);
        $this->assertTrue($assignment1->to_cook);
        $this->assertFalse($assignment2->to_cook);

        // Now move assignment1 to day 3 (after assignment2's day), which should flip the to_cook flags
        $response = $this->actingAs($user)
            ->patch(route('meal-assignments.move', $assignment1), [
                'new_meal_plan_day_id' => $day3->id
            ]);

        // This should fail with 422 due to unique constraint
        $response->assertStatus(422);
    }

    public function test_cannot_move_assignment_to_day_in_different_meal_plan(): void
    {
        // Arrange
        $user = User::factory()->create();
        $mealPlan1 = MealPlan::factory()->create(['user_id' => $user->id]);
        $mealPlan2 = MealPlan::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan1->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan1->id,
            'day_number' => 1
        ]);

        $day2 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan2->id, // Different meal plan
            'day_number' => 1
        ]);

        $assignment = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day1->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Act
        $response = $this->actingAs($user)
            ->patch(route('meal-assignments.move', $assignment), [
                'new_meal_plan_day_id' => $day2->id
            ]);

        // Assert
        $response->assertStatus(422); // Unprocessable Entity
        $this->assertDatabaseHas('meal_assignments', [
            'id' => $assignment->id,
            'meal_plan_day_id' => $day1->id // Not changed
        ]);
    }

    public function test_unauthorized_user_cannot_move_assignment(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $mealPlan = MealPlan::factory()->create(['user_id' => $owner->id]);
        $recipe = Recipe::factory()->create(['user_id' => $owner->id]);

        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1
        ]);

        $day2 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 2
        ]);

        $assignment = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day1->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Act: Try to move assignment as a different user
        $response = $this->actingAs($otherUser)
            ->patch(route('meal-assignments.move', $assignment), [
                'new_meal_plan_day_id' => $day2->id
            ]);

        // Assert
        $response->assertForbidden();
        $this->assertDatabaseHas('meal_assignments', [
            'id' => $assignment->id,
            'meal_plan_day_id' => $day1->id // Not changed
        ]);
    }

    public function test_unauthenticated_user_cannot_move_assignment(): void
    {
        // Arrange
        $user = User::factory()->create();
        $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $mealPlanRecipe = MealPlanRecipe::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'recipe_id' => $recipe->id
        ]);

        $day1 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 1
        ]);

        $day2 = MealPlanDay::factory()->create([
            'meal_plan_id' => $mealPlan->id,
            'day_number' => 2
        ]);

        $assignment = MealAssignment::factory()->create([
            'meal_plan_day_id' => $day1->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'to_cook' => true
        ]);

        // Act: Try to move assignment without authentication
        $response = $this->patch(route('meal-assignments.move', $assignment), [
            'new_meal_plan_day_id' => $day2->id
        ]);

        // Assert
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('meal_assignments', [
            'id' => $assignment->id,
            'meal_plan_day_id' => $day1->id // Not changed
        ]);
    }
}
